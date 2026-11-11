@extends('layouts.app')

@section('title', 'Detail Pesanan: ' . $salesOrder->order_no)

@section('content')
<div class="space-y-6">

    <!-- Top Action & Breadcrumb -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-cream-500 mb-1">
                <a href="{{ route('sales-orders.index') }}" class="hover:underline">Pesanan Penjualan (SO)</a>
                <span>/</span>
                <span class="font-mono text-cream-800">{{ $salesOrder->order_no }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-lg sm:text-xl font-bold text-cream-900">{{ $salesOrder->order_no }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold
                    {{ $salesOrder->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' : ($salesOrder->status === 'PARTIAL_SHIPPED' ? 'bg-amber-100 text-amber-800' : ($salesOrder->status === 'CONFIRMED' ? 'bg-blue-100 text-blue-800' : 'bg-cream-300 text-cream-800')) }}">
                    {{ $salesOrder->status }}
                </span>
            </div>
            <p class="text-xs text-cream-600 mt-0.5">Diterbitkan pada {{ $salesOrder->order_date->format('d F Y') }} oleh {{ $salesOrder->salesUser->name ?? '-' }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('sales-orders.print', $salesOrder->id) }}" target="_blank" class="neo-btn px-3.5 py-2 text-xs font-semibold text-slate-800 hover:bg-slate-100 rounded-xl flex items-center space-x-1.5 shadow-sm">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Surat Pesanan</span>
            </a>

            @if(in_array(Auth::user()->role->code, ['ADMIN', 'LOGISTIK']) && in_array($salesOrder->status, ['CONFIRMED', 'PROCESSING', 'PARTIAL_SHIPPED']) && $salesOrder->pending_backorder_qty > 0)
                <a href="{{ route('delivery-orders.index') }}" class="neo-btn-primary px-3.5 py-2 text-xs font-semibold rounded-xl flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    <span>Proses Surat Jalan (DO)</span>
                </a>
            @endif

            @if(in_array(Auth::user()->role->code, ['ADMIN', 'MARKETING']) && in_array($salesOrder->status, ['CONFIRMED', 'PROCESSING']) && $salesOrder->total_shipped_qty == 0)
                <form method="POST" action="{{ route('sales-orders.cancel', $salesOrder->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Stok yang telah direservasi akan dilepas kembali ke gudang.');">
                    @csrf
                    <button type="submit" class="neo-btn px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50 rounded-xl">
                        Batalkan Pesanan
                    </button>
                </form>
            @endif

            <a href="{{ route('sales-orders.index') }}" class="neo-btn px-3 py-2 text-xs font-medium text-cream-700 rounded-xl">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Customer & Fulfillment Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Customer Details -->
        <div class="neo-card p-4 space-y-2">
            <span class="text-[11px] font-mono uppercase text-cream-500">Informasi Pelanggan</span>
            <h3 class="text-sm font-bold text-cream-900">{{ $salesOrder->customer->name }}</h3>
            <p class="text-xs text-cream-600 font-mono">{{ $salesOrder->customer->code }} | Telp: {{ $salesOrder->customer->phone ?? '-' }}</p>
            <p class="text-xs text-cream-700">{{ $salesOrder->customer->address ?? '-' }}</p>
        </div>

        <!-- Fulfillment Progress -->
        @php
            $totalOrdered = $salesOrder->total_ordered_qty;
            $totalShipped = $salesOrder->total_shipped_qty;
            $pct = $totalOrdered > 0 ? min(100, round(($totalShipped / $totalOrdered) * 100)) : 0;
        @endphp
        <div class="neo-card p-4 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-mono uppercase text-cream-500">Progres Pemenuhan Kirim</span>
                <strong class="text-xs font-mono text-forest">{{ $pct }}%</strong>
            </div>
            <div class="w-full bg-cream-300 h-2.5 rounded-full overflow-hidden">
                <div class="bg-forest h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
            </div>
            <div class="flex items-center justify-between text-xs font-mono pt-1">
                <span class="text-cream-600">Dipesan: {{ number_format($totalOrdered, 0, ',', '.') }}</span>
                <span class="text-emerald-800 font-semibold">Terkirim: {{ number_format($totalShipped, 0, ',', '.') }}</span>
                <span class="text-terracotta font-semibold">Sisa: {{ number_format($salesOrder->pending_backorder_qty, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="neo-card p-4 space-y-1">
            <span class="text-[11px] font-mono uppercase text-cream-500">Total Tagihan Pesanan</span>
            <p class="text-2xl font-mono font-bold text-forest">
                Rp {{ number_format($salesOrder->total_amount, 0, ',', '.') }}
            </p>
            <p class="text-[11px] text-cream-500">Termin pembayaran standar 30 hari kalender</p>
        </div>
    </div>

    <!-- Items Detail Table -->
    <div class="neo-card overflow-hidden">
        <div class="p-4 border-b border-cream-300 bg-cream-100/50">
            <h2 class="text-xs font-bold text-cream-900 uppercase font-mono tracking-wider">
                Rincian Barang Dipesan & Status Pemenuhan (Backorder Tracking)
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-cream-300 text-cream-500 font-mono text-[11px]">
                        <th class="py-3 px-4">Kode SKU</th>
                        <th class="py-3 px-4">Nama Barang</th>
                        <th class="py-3 px-3">Satuan</th>
                        <th class="py-3 px-3 text-right">Dipesan</th>
                        <th class="py-3 px-3 text-right">Terkirim</th>
                        <th class="py-3 px-3 text-right">Sisa Belum Kirim (Backorder)</th>
                        <th class="py-3 px-3 text-right">Harga Satuan</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-300/60 font-mono">
                    @foreach ($salesOrder->items as $item)
                        @php
                            $backorderQty = max(0, (float)$item->qty_ordered - (float)$item->qty_shipped);
                        @endphp
                        <tr class="hover:bg-cream-100/60">
                            <td class="py-3 px-4 font-semibold text-cream-900 whitespace-nowrap">
                                {{ $item->item->code }}
                            </td>
                            <td class="py-3 px-4 font-sans font-medium text-cream-900">
                                {{ $item->item->name }}
                            </td>
                            <td class="py-3 px-3 text-cream-600 whitespace-nowrap">
                                {{ $item->item->unit }}
                            </td>
                            <td class="py-3 px-3 text-right font-semibold text-cream-800">
                                {{ number_format($item->qty_ordered, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right text-emerald-800 font-semibold">
                                {{ number_format($item->qty_shipped, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold {{ $backorderQty > 0 ? 'text-terracotta' : 'text-cream-400' }}">
                                {{ number_format($backorderQty, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right text-cream-700">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-cream-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Related Delivery Orders (Surat Jalan Gudang) -->
    <div class="neo-card p-6 space-y-4">
        <h2 class="text-xs font-bold text-cream-900 uppercase font-mono tracking-wider">
            Riwayat Surat Jalan (Delivery Order) Terbitan Gudang
        </h2>

        @if($salesOrder->deliveryOrders->isEmpty())
            <p class="text-xs text-cream-500 py-2">Belum ada pengiriman barang untuk pesanan ini.</p>
        @else
            <div class="space-y-3">
                @foreach ($salesOrder->deliveryOrders as $do)
                    <div class="neo-card-sm p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs bg-cream-100/60">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('delivery-orders.show', $do->id) }}" class="font-mono font-bold text-forest hover:underline">
                                    {{ $do->do_no }}
                                </a>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold
                                    {{ $do->delivery_status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $do->delivery_status }}
                                </span>
                            </div>
                            <p class="text-cream-600 font-mono text-[11px]">
                                Tanggal Kirim: {{ $do->do_date->format('d/m/Y') }} | Armada: {{ $do->vehicle_no ?? '-' }} (Supir: {{ $do->driver_name ?? '-' }})
                            </p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <a href="{{ route('delivery-orders.show', $do->id) }}" class="neo-btn px-3 py-1.5 text-xs text-cream-800 hover:border-forest/50">
                                Lihat Surat Jalan &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
