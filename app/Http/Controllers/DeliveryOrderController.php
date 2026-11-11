<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Services\AuditTrailService;
use App\Services\DocumentNumberService;
use App\Services\InventoryService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DeliveryOrderController extends Controller
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

        $query = DeliveryOrder::with(['customer', 'salesOrder', 'logisticsUser', 'items.item']);

        if ($status && $status !== 'ALL') {
            $query->where('delivery_status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('do_no', 'like', "%{$search}%")
                  ->orWhere('driver_name', 'like', "%{$search}%")
                  ->orWhere('vehicle_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $deliveryOrders = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Ambil Sales Orders yang masih memiliki sisa barang yang belum dikirim
        $eligibleOrders = SalesOrder::with(['customer', 'items.item'])
            ->whereIn('status', ['CONFIRMED', 'PROCESSING', 'PARTIAL_SHIPPED'])
            ->whereHas('items', function ($q) {
                $q->whereRaw('qty_ordered > qty_shipped');
            })
            ->orderBy('order_date', 'asc')
            ->get();

        return view('delivery_orders.index', compact('deliveryOrders', 'eligibleOrders', 'status', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'sales_order_id' => ['required', 'exists:sales_orders,id'],
            'do_date' => ['required', 'date'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'vehicle_no' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.so_item_id' => ['required', 'exists:sales_order_items,id'],
            'items.*.qty_delivered' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $do = DB::transaction(function () use ($request) {
                $so = SalesOrder::with('items.item')->lockForUpdate()->findOrFail($request->input('sales_order_id'));

                if (in_array($so->status, ['COMPLETED', 'CANCELLED'])) {
                    throw new Exception("Sales Order {$so->order_no} berstatus {$so->status} dan tidak dapat diproses kirim.");
                }

                $doNo = $this->docNumberService->generate('DO');

                $deliveryOrder = DeliveryOrder::create([
                    'do_no' => $doNo,
                    'do_date' => $request->input('do_date'),
                    'sales_order_id' => $so->id,
                    'customer_id' => $so->customer_id,
                    'logistics_user_id' => Auth::id(),
                    'driver_name' => $request->input('driver_name'),
                    'vehicle_no' => $request->input('vehicle_no'),
                    'delivery_status' => 'IN_TRANSIT',
                    'notes' => $request->input('notes'),
                ]);

                $hasValidItem = false;

                foreach ($request->input('items') as $row) {
                    $qtyDeliver = (float)$row['qty_delivered'];
                    if ($qtyDeliver <= 0) {
                        continue;
                    }

                    $soItem = SalesOrderItem::where('id', $row['so_item_id'])
                        ->where('sales_order_id', $so->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $remaining = (float)$soItem->qty_ordered - (float)$soItem->qty_shipped;
                    if ($qtyDeliver > $remaining) {
                        throw new Exception("Kuantitas pengiriman ({$qtyDeliver}) untuk barang {$soItem->item->name} melebihi sisa pesanan ({$remaining})!");
                    }

                    $hasValidItem = true;

                    // Buat DO item
                    DeliveryOrderItem::create([
                        'delivery_order_id' => $deliveryOrder->id,
                        'sales_order_item_id' => $soItem->id,
                        'item_id' => $soItem->item_id,
                        'qty_delivered' => $qtyDeliver,
                    ]);

                    // Update qty_shipped di SO Item
                    $soItem->qty_shipped = (float)$soItem->qty_shipped + $qtyDeliver;
                    $soItem->save();

                    // Potong stok gudang (on_hand & reserved berkurang) dan catat ledger
                    $this->inventoryService->shipStock(
                        $soItem->item_id,
                        $qtyDeliver,
                        Auth::id(),
                        $doNo,
                        $deliveryOrder->id,
                        "Pengiriman DO {$doNo} atas pesanan {$so->order_no}"
                    );
                }

                if (!$hasValidItem) {
                    throw new Exception("Paling sedikit satu barang harus dikirim dengan kuantitas lebih dari 0!");
                }

                // Cek status pemenuhan SO: apakah semua item sudah terkirim penuh?
                $so->refresh();
                $allShipped = true;
                foreach ($so->items as $item) {
                    if ((float)$item->qty_shipped < (float)$item->qty_ordered) {
                        $allShipped = false;
                        break;
                    }
                }

                $so->status = $allShipped ? 'COMPLETED' : 'PARTIAL_SHIPPED';
                $so->save();

                $this->auditTrailService->log(
                    'CREATE_DELIVERY_ORDER',
                    'delivery_orders',
                    $deliveryOrder->id,
                    null,
                    $deliveryOrder->toArray(),
                    Auth::id()
                );

                return $deliveryOrder;
            });

            return redirect()->route('delivery-orders.show', $do->id)
                ->with('success', "Surat Jalan {$do->do_no} berhasil diterbitkan dan stok barang telah dipotong dari gudang!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(DeliveryOrder $deliveryOrder): View
    {
        $deliveryOrder->load(['customer', 'salesOrder', 'logisticsUser', 'items.item']);
        return view('delivery_orders.show', compact('deliveryOrder'));
    }

    public function updateStatus(Request $request, DeliveryOrder $deliveryOrder): RedirectResponse
    {
        $request->validate([
            'delivery_status' => ['required', 'in:PREPARING,IN_TRANSIT,DELIVERED,RETURNED'],
            'recipient_name' => ['nullable', 'string', 'max:100'],
        ]);

        $status = $request->input('delivery_status');
        $deliveryOrder->delivery_status = $status;

        if ($status === 'DELIVERED') {
            $deliveryOrder->delivered_at = Carbon::now();
            $deliveryOrder->recipient_name = $request->input('recipient_name') ?? $deliveryOrder->customer->name;
        }

        $deliveryOrder->save();

        $this->auditTrailService->log(
            'UPDATE_DO_STATUS',
            'delivery_orders',
            $deliveryOrder->id,
            null,
            ['status' => $status],
            Auth::id()
        );

        return back()->with('success', "Status Surat Jalan {$deliveryOrder->do_no} berhasil diperbarui menjadi {$status}.");
    }
}
