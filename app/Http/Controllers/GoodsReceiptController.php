<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\AuditTrailService;
use App\Services\DocumentNumberService;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    public function __construct(
        protected DocumentNumberService $docNumberService,
        protected InventoryService $inventoryService,
        protected AuditTrailService $auditTrailService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = GoodsReceipt::with(['supplier', 'purchaseOrder', 'logisticsUser', 'items.item']);

        if ($status && $status !== 'ALL') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('grn_no', 'like', "%{$search}%")
                  ->orWhere('supplier_do_no', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $goodsReceipts = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $openPurchaseOrders = PurchaseOrder::with(['supplier', 'items.item'])
            ->whereIn('status', ['ISSUED', 'PARTIAL_RECEIVED'])
            ->whereHas('items', function ($q) {
                $q->whereRaw('qty_ordered > qty_received');
            })
            ->orderBy('po_date', 'asc')
            ->get();

        return view('goods_receipts.index', compact('goodsReceipts', 'openPurchaseOrders', 'status', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            'receipt_date' => ['required', 'date'],
            'supplier_do_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.po_item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.qty_received' => ['required', 'numeric', 'min:0'],
            'items.*.qty_accepted' => ['required', 'numeric', 'min:0'],
            'items.*.qty_rejected' => ['nullable', 'numeric', 'min:0'],
            'items.*.reject_reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $grn = DB::transaction(function () use ($request) {
                $po = PurchaseOrder::with('items.item')->lockForUpdate()->findOrFail($request->input('purchase_order_id'));

                if (in_array($po->status, ['COMPLETED', 'CANCELLED'])) {
                    throw new Exception("Purchase Order {$po->po_no} berstatus {$po->status} dan tidak dapat diproses penerimaan.");
                }

                $grnNo = $this->docNumberService->generate('GRN');

                $goodsReceipt = GoodsReceipt::create([
                    'grn_no' => $grnNo,
                    'receipt_date' => $request->input('receipt_date'),
                    'purchase_order_id' => $po->id,
                    'supplier_id' => $po->supplier_id,
                    'logistics_user_id' => Auth::id(),
                    'supplier_do_no' => $request->input('supplier_do_no'),
                    'status' => 'ACCEPTED',
                    'notes' => $request->input('notes'),
                ]);

                $hasReceived = false;

                foreach ($request->input('items') as $row) {
                    $qtyReceived = (float)$row['qty_received'];
                    $qtyAccepted = (float)$row['qty_accepted'];
                    $qtyRejected = (float)($row['qty_rejected'] ?? 0);

                    if ($qtyReceived <= 0) {
                        continue;
                    }

                    if ($qtyAccepted + $qtyRejected !== $qtyReceived) {
                        throw new Exception("Jumlah kuantitas diterima ({$qtyReceived}) harus sama dengan kuantitas lolos ({$qtyAccepted}) ditambah kuantitas ditolak ({$qtyRejected})!");
                    }

                    $poItem = PurchaseOrderItem::where('id', $row['po_item_id'])
                        ->where('purchase_order_id', $po->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $remaining = (float)$poItem->qty_ordered - (float)$poItem->qty_received;
                    if ($qtyAccepted > $remaining) {
                        throw new Exception("Kuantitas barang diterima ({$qtyAccepted}) untuk {$poItem->item->name} melebihi sisa PO ({$remaining})!");
                    }

                    $hasReceived = true;

                    // Buat GRN Item
                    GoodsReceiptItem::create([
                        'goods_receipt_id' => $goodsReceipt->id,
                        'purchase_order_item_id' => $poItem->id,
                        'item_id' => $poItem->item_id,
                        'qty_received' => $qtyReceived,
                        'qty_accepted' => $qtyAccepted,
                        'qty_rejected' => $qtyRejected,
                        'reject_reason' => $row['reject_reason'] ?? null,
                    ]);

                    // Update PO Item
                    $poItem->qty_received = (float)$poItem->qty_received + $qtyAccepted;
                    $poItem->save();

                    // Tambah stok fisik di gudang untuk barang yang diterima lolos inspeksi
                    if ($qtyAccepted > 0) {
                        $this->inventoryService->receiveStock(
                            $poItem->item_id,
                            $qtyAccepted,
                            Auth::id(),
                            $grnNo,
                            $goodsReceipt->id,
                            "Penerimaan barang GRN {$grnNo} atas PO {$po->po_no}"
                        );
                    }
                }

                if (!$hasReceived) {
                    throw new Exception("Paling sedikit satu barang harus diterima dengan kuantitas lebih dari 0!");
                }

                // Cek status pemenuhan PO
                $po->refresh();
                $allReceived = true;
                foreach ($po->items as $item) {
                    if ((float)$item->qty_received < (float)$item->qty_ordered) {
                        $allReceived = false;
                        break;
                    }
                }

                $po->status = $allReceived ? 'COMPLETED' : 'PARTIAL_RECEIVED';
                $po->save();

                $this->auditTrailService->log(
                    'CREATE_GOODS_RECEIPT',
                    'goods_receipts',
                    $goodsReceipt->id,
                    null,
                    $goodsReceipt->toArray(),
                    Auth::id()
                );

                return $goodsReceipt;
            });

            return redirect()->route('goods-receipts.show', $grn->id)
                ->with('success', "Nota Penerimaan Gudang {$grn->grn_no} berhasil diterbitkan dan stok barang masuk telah bertambah!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(GoodsReceipt $goodsReceipt): View
    {
        $goodsReceipt->load(['supplier', 'purchaseOrder', 'logisticsUser', 'items.item']);
        return view('goods_receipts.show', compact('goodsReceipt'));
    }
}
