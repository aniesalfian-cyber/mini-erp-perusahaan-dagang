<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Services\AuditTrailService;
use App\Services\DocumentNumberService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected DocumentNumberService $docNumberService,
        protected AuditTrailService $auditTrailService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = PurchaseOrder::with(['supplier', 'purchasingUser', 'items.item']);

        if ($status && $status !== 'ALL') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('po_no', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $purchaseOrders = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $suppliers = Supplier::where('is_active', true)->orderBy('name', 'asc')->get();
        $items = Item::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('purchase_orders.index', compact('purchaseOrders', 'suppliers', 'items', 'status', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'po_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:po_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $po = DB::transaction(function () use ($request) {
                $poNo = $this->docNumberService->generate('PO');
                $totalAmount = 0;
                $lines = [];

                foreach ($request->input('items') as $row) {
                    $qty = (float)$row['qty'];
                    $cost = (float)$row['unit_cost'];
                    $subtotal = $qty * $cost;
                    $totalAmount += $subtotal;

                    $lines[] = [
                        'item_id' => (int)$row['item_id'],
                        'qty_ordered' => $qty,
                        'qty_received' => 0,
                        'unit_cost' => $cost,
                        'subtotal' => $subtotal,
                        'notes' => $row['notes'] ?? null,
                    ];
                }

                $purchaseOrder = PurchaseOrder::create([
                    'po_no' => $poNo,
                    'po_date' => $request->input('po_date'),
                    'supplier_id' => (int)$request->input('supplier_id'),
                    'purchasing_user_id' => Auth::id(),
                    'total_amount' => $totalAmount,
                    'status' => 'ISSUED',
                    'expected_date' => $request->input('expected_date'),
                    'notes' => $request->input('notes'),
                ]);

                foreach ($lines as $line) {
                    $purchaseOrder->items()->create($line);
                }

                $this->auditTrailService->log(
                    'CREATE_PURCHASE_ORDER',
                    'purchase_orders',
                    $purchaseOrder->id,
                    null,
                    $purchaseOrder->toArray(),
                    Auth::id()
                );

                return $purchaseOrder;
            });

            return redirect()->route('purchase-orders.show', $po->id)
                ->with('success', "Pesanan Pengadaan {$po->po_no} berhasil diterbitkan ke pemasok!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'purchasingUser', 'items.item.stockBalance', 'goodsReceipts.items.item']);
        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function print(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'purchasingUser', 'items.item']);
        return view('purchase_orders.print', compact('purchaseOrder'));
    }
}
