<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Item;
use App\Models\SalesOrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BackorderController extends Controller
{
    public function index(Request $request): View
    {
        $customerId = $request->query('customer_id');
        $itemId = $request->query('item_id');
        $search = $request->query('search');

        $query = SalesOrderItem::with(['salesOrder.customer', 'salesOrder.salesUser', 'item.stockBalance'])
            ->whereRaw('qty_ordered > qty_shipped')
            ->whereHas('salesOrder', function ($q) use ($customerId, $search) {
                $q->whereIn('status', ['CONFIRMED', 'PROCESSING', 'PARTIAL_SHIPPED']);

                if ($customerId) {
                    $q->where('customer_id', $customerId);
                }

                if ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('order_no', 'like', "%{$search}%")
                            ->orWhereHas('customer', function ($c) use ($search) {
                                $c->where('name', 'like', "%{$search}%");
                            });
                    });
                }
            });

        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        $backorders = $query->orderBy('sales_order_id', 'asc')->paginate(20)->withQueryString();

        // Ringkasan metrik backorder
        $allPending = SalesOrderItem::whereRaw('qty_ordered > qty_shipped')
            ->whereHas('salesOrder', function ($q) {
                $q->whereIn('status', ['CONFIRMED', 'PROCESSING', 'PARTIAL_SHIPPED']);
            })
            ->get();

        $totalPendingLines = $allPending->count();
        $totalPendingQty = $allPending->sum(fn ($i) => (float)$i->qty_ordered - (float)$i->qty_shipped);
        $totalPendingValue = $allPending->sum(fn ($i) => ((float)$i->qty_ordered - (float)$i->qty_shipped) * (float)$i->unit_price);

        $customers = Customer::where('is_active', true)->orderBy('name', 'asc')->get();
        $items = Item::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('backorders.index', compact(
            'backorders',
            'customers',
            'items',
            'customerId',
            'itemId',
            'search',
            'totalPendingLines',
            'totalPendingQty',
            'totalPendingValue'
        ));
    }

    public function exportCsv(Request $request): Response
    {
        $query = SalesOrderItem::with(['salesOrder.customer', 'item'])
            ->whereRaw('qty_ordered > qty_shipped')
            ->whereHas('salesOrder', function ($q) {
                $q->whereIn('status', ['CONFIRMED', 'PROCESSING', 'PARTIAL_SHIPPED']);
            })
            ->orderBy('sales_order_id', 'asc');

        $items = $query->get();

        $csvHeader = [
            'Nomor Pesanan (SO)',
            'Tanggal Pesan',
            'Nama Pelanggan',
            'Kode Barang',
            'Nama Barang',
            'Satuan',
            'Qty Dipesan',
            'Qty Terkirim',
            'Qty Belum Dikirim (Backorder)',
            'Harga Satuan (Rp)',
            'Nilai Backorder (Rp)',
            'Umur Pesanan (Hari)',
        ];

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $csvHeader);

        foreach ($items as $line) {
            $pending = (float)$line->qty_ordered - (float)$line->qty_shipped;
            $pendingVal = $pending * (float)$line->unit_price;
            $orderAge = Carbon::now()->diffInDays($line->salesOrder->order_date);

            fputcsv($output, [
                $line->salesOrder->order_no,
                $line->salesOrder->order_date->format('Y-m-d'),
                $line->salesOrder->customer->name,
                $line->item->code,
                $line->item->name,
                $line->item->unit,
                $line->qty_ordered,
                $line->qty_shipped,
                $pending,
                $line->unit_price,
                $pendingVal,
                $orderAge,
            ]);
        }

        rewind($output);
        $csvData = stream_get_contents($output);
        fclose($output);

        $filename = 'Laporan_Barang_PO_Belum_Dikirim_' . date('Ymd_His') . '.csv';

        return response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
