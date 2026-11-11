<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\GoodsReceipt;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockBalance;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $role = $user->role->code ?? 'ADMIN';

        // 1. Ringkasan Keuangan & Operasional Global
        $totalSales = (float)SalesOrder::whereNotIn('status', ['CANCELLED'])->sum('total_amount');
        $totalInvoiced = (float)SalesInvoice::sum('total_amount');
        $totalPaid = (float)SalesInvoice::sum('paid_amount');
        $totalReceivable = max(0, $totalInvoiced - $totalPaid);

        // 2. Ringkasan Backorder (Barang PO Belum Dikirimkan)
        $backorderQuery = SalesOrderItem::whereRaw('qty_ordered > qty_shipped')
            ->whereHas('salesOrder', function ($q) {
                $q->whereIn('status', ['CONFIRMED', 'PROCESSING', 'PARTIAL_SHIPPED']);
            });

        $totalBackorderLines = $backorderQuery->count();
        $totalBackorderQty = (float)$backorderQuery->selectRaw('SUM(qty_ordered - qty_shipped) as pending')->value('pending') ?? 0;

        // 3. Stok Kritis di Bawah Minimum
        $lowStockItems = Item::with('stockBalance')
            ->whereHas('stockBalance', function ($q) {
                $q->whereRaw('available <= items.min_stock');
            })
            ->get();

        // 4. Data Operasional Antar Divisi
        $openOrdersCount = SalesOrder::whereIn('status', ['DRAFT', 'CONFIRMED', 'PROCESSING'])->count();
        $pendingDOCount = DeliveryOrder::whereIn('delivery_status', ['PREPARING', 'IN_TRANSIT'])->count();
        $openPOCount = PurchaseOrder::whereIn('status', ['DRAFT', 'ISSUED', 'PARTIAL_RECEIVED'])->count();
        $unpaidInvoicesCount = SalesInvoice::whereIn('payment_status', ['UNPAID', 'PARTIAL'])->count();

        // 5. Data Grafik Penjualan Bulanan (6 Bulan Terakhir untuk Chart.js)
        $monthlySales = [];
        $monthLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthLabels[] = $month->translatedFormat('M Y');
            $sum = (float)SalesOrder::whereYear('order_date', $month->year)
                ->whereMonth('order_date', $month->month)
                ->whereNotIn('status', ['CANCELLED'])
                ->sum('total_amount');
            $monthlySales[] = $sum;
        }

        // 6. Data Pelanggan & Pemasok dengan Koordinat Peta (Leaflet)
        $mapLocations = [];
        $customers = Customer::whereNotNull('latitude')->whereNotNull('longitude')->get();
        foreach ($customers as $c) {
            $mapLocations[] = [
                'type' => 'customer',
                'name' => $c->name,
                'code' => $c->code,
                'lat' => (float)$c->latitude,
                'lng' => (float)$c->longitude,
                'phone' => $c->phone,
                'address' => $c->address,
                'credit_limit' => $c->credit_limit,
                'current_credit' => $c->current_credit,
            ];
        }

        $suppliers = Supplier::whereNotNull('latitude')->whereNotNull('longitude')->get();
        foreach ($suppliers as $s) {
            $mapLocations[] = [
                'type' => 'supplier',
                'name' => $s->name,
                'code' => $s->code,
                'lat' => (float)$s->latitude,
                'lng' => (float)$s->longitude,
                'phone' => $s->phone,
                'address' => $s->address,
                'contact_person' => $s->contact_person,
            ];
        }

        // 7. Recent Transactions
        $recentOrders = SalesOrder::with(['customer', 'salesUser'])->orderBy('id', 'desc')->take(5)->get();
        $recentDOs = DeliveryOrder::with(['customer', 'salesOrder'])->orderBy('id', 'desc')->take(5)->get();

        return view('dashboard.index', compact(
            'user',
            'role',
            'totalSales',
            'totalInvoiced',
            'totalPaid',
            'totalReceivable',
            'totalBackorderLines',
            'totalBackorderQty',
            'lowStockItems',
            'openOrdersCount',
            'pendingDOCount',
            'openPOCount',
            'unpaidInvoicesCount',
            'monthLabels',
            'monthlySales',
            'mapLocations',
            'recentOrders',
            'recentDOs'
        ));
    }
}
