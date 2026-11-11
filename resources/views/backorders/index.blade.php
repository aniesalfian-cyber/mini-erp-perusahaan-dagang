@extends('layouts.app')

@section('title', 'Laporan Barang PO Belum Dikirimkan (Backorder)')

@section('content')
<div class="space-y-6">

    <!-- Header Action Bar -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-l-4 border-l-terracotta">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-semibold bg-terracotta text-white uppercase tracking-wider">
                    Laporan Operasional
                </span>
                <span class="text-xs text-cream-500 font-mono">Real-time Outstanding Backorder</span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-cream-900 mt-1">
                Laporan Barang PO Belum Dikirimkan (Outstanding Backorder)
            </h1>
            <p class="text-xs text-cream-600 mt-0.5">
                Monitoring komprehensif seluruh antrean pemenuhan barang dari Sales Order aktif yang belum terkirim ke pelanggan.
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('backorders.export-csv') }}" class="neo-btn px-4 py-2 text-xs font-semibold text-cream-800 hover:border-forest/50 flex items-center space-x-1.5 rounded-xl shadow-neo-sm">
                <svg class="w-4 h-4 text-forest" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Ekspor File CSV / Excel</span>
            </a>
        </div>
    </div>

    <!-- 3 Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="neo-card p-5 space-y-1">
            <span class="text-xs font-medium text-cream-600">Total Baris Antrean Tertunda</span>
            <p class="text-2xl font-mono font-bold text-cream-900">{{ number_format($totalPendingLines, 0, ',', '.') }} <span class="text-xs font-normal font-sans">Baris</span></p>
            <p class="text-[11px] text-cream-500">Item barang dari seluruh PO berjalan</p>
        </div>

        <div class="neo-card p-5 space-y-1 border-l-4 border-l-terracotta">
            <span class="text-xs font-medium text-terracotta">Total Kuantitas Fisik Belum Kirim</span>
            <p class="text-2xl font-mono font-bold text-terracotta">{{ number_format($totalPendingQty, 0, ',', '.') }} <span class="text-xs font-normal font-sans">Unit</span></p>
            <p class="text-[11px] text-cream-500">Akumulasi kuantitas belum terpenuhi</p>
        </div>

        <div class="neo-card p-5 space-y-1">
            <span class="text-xs font-medium text-cream-600">Estimasi Nilai Backorder Tertahan</span>
            <p class="text-2xl font-mono font-bold text-forest">Rp {{ number_format($totalPendingValue, 0, ',', '.') }}</p>
            <p class="text-[11px] text-cream-500">Potensi piutang belum dapat ditagihkan</p>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="neo-card p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <form method="GET" action="{{ route('backorders.index') }}" class="flex-1 flex flex-col sm:flex-row items-center gap-2 w-full">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor SO atau nama pelanggan..."
                       class="neo-input w-full pl-9 pr-3 py-2 text-xs">
                <svg class="w-4 h-4 text-cream-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <select name="customer_id" class="neo-input px-3 py-2 text-xs w-full sm:w-auto">
                    <option value="">Semua Pelanggan</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>

                <select name="item_id" class="neo-input px-3 py-2 text-xs w-full sm:w-auto">
                    <option value="">Semua Barang</option>
                    @foreach ($items as $itm)
                        <option value="{{ $itm->id }}" {{ $itemId == $itm->id ? 'selected' : '' }}>{{ $itm->code }} - {{ $itm->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="neo-btn px-4 py-2 font-medium text-cream-800 hover:border-forest/50">
                    Saring
                </button>

                @if($search || $customerId || $itemId)
                    <a href="{{ route('backorders.index') }}" class="neo-btn px-3 py-2 text-rose-700 hover:bg-rose-50" title="Reset Filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Backorders Detail Table -->
    <div class="neo-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-cream-300 bg-cream-100/60 font-mono text-cream-600 uppercase text-[11px]">
                        <th class="py-3 px-4">No. Order (SO)</th>
                        <th class="py-3 px-3">Tgl / Umur</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Kode & Nama Barang</th>
                        <th class="py-3 px-3 text-right">Dipesan</th>
                        <th class="py-3 px-3 text-right">Terkirim</th>
                        <th class="py-3 px-3 text-right font-bold text-terracotta">Sisa Backorder</th>
                        <th class="py-3 px-3 text-right">Stok Fisik Gudang</th>
                        <th class="py-3 px-4 text-right">Nilai Tertahan</th>
                        <th class="py-3 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-300/60 font-mono">
                    @forelse ($backorders as $line)
                        @php
                            $so = $line->salesOrder;
                            $pendingQty = max(0, (float)$line->qty_ordered - (float)$line->qty_shipped);
                            $pendingVal = $pendingQty * (float)$line->unit_price;
                            $ageDays = Carbon\Carbon::now()->diffInDays($so->order_date);
                            $stockOnHand = (float)($line->item->stockBalance->on_hand ?? 0);
                            $stockEnough = $stockOnHand >= $pendingQty;
                        @endphp
                        <tr class="hover:bg-cream-100/70 transition-colors {{ $ageDays > 7 ? 'bg-amber-50/20' : '' }}">
                            <td class="py-3 px-4 font-semibold text-cream-900 whitespace-nowrap">
                                <a href="{{ route('sales-orders.show', $so->id) }}" class="text-forest hover:underline">
                                    {{ $so->order_no }}
                                </a>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="text-cream-700 block">{{ $so->order_date->format('d/m/Y') }}</span>
                                <span class="text-[10px] {{ $ageDays > 5 ? 'text-amber-700 font-semibold' : 'text-cream-500' }}">
                                    {{ $ageDays }} hari lalu
                                </span>
                            </td>
                            <td class="py-3 px-4 font-sans font-medium text-cream-900 min-w-[140px]">
                                {{ $so->customer->name }}
                            </td>
                            <td class="py-3 px-4 font-sans">
                                <span class="font-bold text-cream-900 block truncate max-w-[160px]">{{ $line->item->name }}</span>
                                <span class="text-[10px] text-cream-500 font-mono">{{ $line->item->code }} ({{ $line->item->unit }})</span>
                            </td>
                            <td class="py-3 px-3 text-right text-cream-700">
                                {{ number_format($line->qty_ordered, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right text-emerald-800 font-semibold">
                                {{ number_format($line->qty_shipped, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-terracotta text-sm">
                                {{ number_format($pendingQty, 0, ',', '.') }} {{ $line->item->unit }}
                            </td>
                            <td class="py-3 px-3 text-right whitespace-nowrap">
                                <span class="font-semibold {{ $stockEnough ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ number_format($stockOnHand, 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] block {{ $stockEnough ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $stockEnough ? 'Cukup di rak' : 'Stok kurang!' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-cream-900 whitespace-nowrap">
                                Rp {{ number_format($pendingVal, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                @if(in_array(Auth::user()->role->code, ['ADMIN', 'LOGISTIK']) && $stockEnough)
                                    <a href="{{ route('delivery-orders.index') }}" class="neo-btn px-2 py-1 text-[11px] font-sans font-semibold text-forest hover:border-forest/50" title="Kirim dengan Surat Jalan">
                                        Kirim DO
                                    </a>
                                @elseif(!$stockEnough)
                                    <a href="{{ route('purchase-orders.index') }}" class="neo-btn px-2 py-1 text-[11px] font-sans font-semibold text-rose-700 hover:bg-rose-50" title="Stok gudang tidak cukup, buat PO Supplier">
                                        Beli PO
                                    </a>
                                @else
                                    <a href="{{ route('sales-orders.show', $so->id) }}" class="neo-btn px-2 py-1 text-[11px] font-sans text-cream-700 hover:text-cream-900">
                                        Lihat SO
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-emerald-700 font-sans">
                                Luar biasa! Tidak ada antrean barang PO yang tertunda (Seluruh pesanan telah terpenuhi).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-cream-300">
            {{ $backorders->links() }}
        </div>
    </div>

</div>
@endsection
