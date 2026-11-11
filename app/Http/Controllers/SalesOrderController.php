<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Item;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Services\AuditTrailService;
use App\Services\CreditCheckService;
use App\Services\DocumentNumberService;
use App\Services\InventoryService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    public function __construct(
        protected DocumentNumberService $docNumberService,
        protected InventoryService $inventoryService,
        protected CreditCheckService $creditCheckService,
        protected AuditTrailService $auditTrailService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = SalesOrder::with(['customer', 'salesUser', 'items.item']);

        if ($status && $status !== 'ALL') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $customers = Customer::where('is_active', true)->orderBy('name', 'asc')->get();
        $items = Item::with('stockBalance')->where('is_active', true)->orderBy('name', 'asc')->get();

        return view('sales_orders.index', compact('orders', 'customers', 'items', 'status', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'order_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $order = DB::transaction(function () use ($request) {
                $customerId = (int)$request->input('customer_id');
                $customer = Customer::findOrFail($customerId);

                // Hitung total order
                $totalAmount = 0;
                $lines = [];
                foreach ($request->input('items') as $row) {
                    $qty = (float)$row['qty'];
                    $price = (float)$row['unit_price'];
                    $subtotal = $qty * $price;
                    $totalAmount += $subtotal;
                    $lines[] = [
                        'item_id' => (int)$row['item_id'],
                        'qty_ordered' => $qty,
                        'qty_shipped' => 0,
                        'unit_price' => $price,
                        'subtotal' => $subtotal,
                        'notes' => $row['notes'] ?? null,
                    ];
                }

                // 1. Validasi Plafon Kredit Pelanggan
                $this->creditCheckService->assertCanPlaceOrder($customerId, $totalAmount);

                // 2. Generate Nomor SO
                $orderNo = $this->docNumberService->generate('SO');

                $order = SalesOrder::create([
                    'order_no' => $orderNo,
                    'order_date' => $request->input('order_date'),
                    'customer_id' => $customerId,
                    'sales_user_id' => Auth::id(),
                    'total_amount' => $totalAmount,
                    'status' => 'CONFIRMED',
                    'notes' => $request->input('notes'),
                ]);

                // 3. Simpan Item & Reservasi Stok
                foreach ($lines as $line) {
                    $orderItem = $order->items()->create($line);

                    // Reservasi stok gudang
                    $this->inventoryService->reserveStock(
                        $line['item_id'],
                        $line['qty_ordered'],
                        Auth::id(),
                        $orderNo,
                        $order->id
                    );
                }

                $this->auditTrailService->log(
                    'CREATE_SALES_ORDER',
                    'sales_orders',
                    $order->id,
                    null,
                    $order->toArray(),
                    Auth::id()
                );

                return $order;
            });

            return redirect()->route('sales-orders.show', $order->id)
                ->with('success', "Pesanan Penjualan {$order->order_no} berhasil dikonfirmasi dan stok telah direservasi!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(SalesOrder $salesOrder): View
    {
        $salesOrder->load(['customer', 'salesUser', 'items.item.stockBalance', 'deliveryOrders.items.item']);
        return view('sales_orders.show', compact('salesOrder'));
    }

    public function print(SalesOrder $salesOrder): View
    {
        $salesOrder->load(['customer', 'salesUser', 'items.item']);
        return view('sales_orders.print', compact('salesOrder'));
    }

    public function cancel(SalesOrder $salesOrder): RedirectResponse
    {
        if (in_array($salesOrder->status, ['COMPLETED', 'CANCELLED'])) {
            return back()->with('error', "Pesanan dengan status {$salesOrder->status} tidak dapat dibatalkan.");
        }

        if ($salesOrder->deliveryOrders()->whereIn('delivery_status', ['IN_TRANSIT', 'DELIVERED'])->exists()) {
            return back()->with('error', "Pesanan tidak dapat dibatalkan karena memiliki Surat Jalan yang sudah terkirim!");
        }

        DB::transaction(function () use ($salesOrder) {
            // Release sisa reservasi
            foreach ($salesOrder->items as $item) {
                $unshipped = max(0, (float)$item->qty_ordered - (float)$item->qty_shipped);
                if ($unshipped > 0) {
                    $this->inventoryService->releaseReservedStock(
                        $item->item_id,
                        $unshipped,
                        Auth::id(),
                        $salesOrder->order_no,
                        $salesOrder->id
                    );
                }
            }

            $salesOrder->status = 'CANCELLED';
            $salesOrder->save();

            $this->auditTrailService->log(
                'CANCEL_SALES_ORDER',
                'sales_orders',
                $salesOrder->id,
                null,
                ['status' => 'CANCELLED'],
                Auth::id()
            );
        });

        return back()->with('success', "Pesanan Penjualan {$salesOrder->order_no} berhasil dibatalkan dan alokasi reservasi stok telah dilepas.");
    }
}
