@extends('layouts.app')

@section('title', 'Detail PO: ' . $purchaseOrder->po_no)

@section('content')
<div class="space-y-6">

    <!-- Top Action & Breadcrumb -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-cream-500 mb-1">
                <a href="{{ route('purchase-orders.index') }}" class="hover:underline">Purchase Order (PO)</a>
                <span>/</span>
                <span class="font-mono text-cream-800">{{ $purchaseOrder->po_no }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-lg sm:text-xl font-bold text-cream-900">{{ $purchaseOrder->po_no }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold
                    {{ $purchaseOrder->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' : ($purchaseOrder->status === 'PARTIAL_RECEIVED' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                    {{ $purchaseOrder->status }}
                </span>
            </div>
            <p class="text-xs text-cream-600 mt-0.5">Diterbitkan pada {{ $purchaseOrder->po_date->format('d F Y') }} oleh {{ $purchaseOrder->purchasingUser->name ?? '-' }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('purchase-orders.print', $purchaseOrder->id) }}" target="_blank" class="neo-btn px-3.5 py-2 text-xs font-semibold text-slate-800 hover:bg-slate-100 rounded-xl flex items-center space-x-1.5 shadow-sm">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Purchase Order</span>
            </a>

            @if(in_array(Auth::user()->role->code, ['ADMIN', 'LOGISTIK']) && in_array($purchaseOrder->status, ['ISSUED', 'PARTIAL_RECEIVED']))
                <a href="{{ route('goods-receipts.index') }}" class="neo-btn-primary px-3.5 py-2 text-xs font-semibold rounded-xl flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Terima Barang Gudang (GRN)</span>
                </a>
            @endif

            <a href="{{ route('purchase-orders.index') }}" class="neo-btn px-3 py-2 text-xs font-medium text-cream-700 rounded-xl">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Supplier & Summary Information -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Supplier Details -->
        <div class="neo-card p-4 space-y-2">
            <span class="text-[11px] font-mono uppercase text-cream-500">Pemasok (Supplier)</span>
            <h3 class="text-sm font-bold text-cream-900">{{ $purchaseOrder->supplier->name }}</h3>
            <p class="text-xs text-cream-600 font-mono">{{ $purchaseOrder->supplier->code }} | Telp: {{ $purchaseOrder->supplier->phone ?? '-' }}</p>
            <p class="text-xs text-cream-700">PIC: {{ $purchaseOrder->supplier->contact_person ?? '-' }}</p>
            <p class="text-xs text-cream-600 truncate">{{ $purchaseOrder->supplier->address ?? '-' }}</p>
        </div>

        <!-- Timeline Details -->
        <div class="neo-card p-4 space-y-2">
            <span class="text-[11px] font-mono uppercase text-cream-500">Jadwal & Kedatangan</span>
            <p class="text-xs text-cream-700">Tanggal Terbit: <strong class="font-mono text-cream-900">{{ $purchaseOrder->po_date->format('d/m/Y') }}</strong></p>
            <p class="text-xs text-cream-700">Target Tiba: <strong class="font-mono text-cream-900">{{ $purchaseOrder->expected_date ? $purchaseOrder->expected_date->format('d/m/Y') : '-' }}</strong></p>
            <p class="text-[11px] text-cream-500 font-sans">Petugas Purchasing: {{ $purchaseOrder->purchasingUser->name ?? '-' }}</p>
        </div>

        <!-- Total Cost -->
        <div class="neo-card p-4 space-y-1">
            <span class="text-[11px] font-mono uppercase text-cream-500">Total Biaya Pembelian</span>
            <p class="text-2xl font-mono font-bold text-cream-900">
                Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}
            </p>
            <p class="text-[11px] text-cream-500">Nilai invoice tagihan supplier setelah barang diterima penuh</p>
        </div>
    </div>

    <!-- Items Detail Table -->
    <div class="neo-card overflow-hidden">
        <div class="p-4 border-b border-cream-300 bg-cream-100/50">
            <h2 class="text-xs font-bold text-cream-900 uppercase font-mono tracking-wider">
                Rincian Barang yang Dipesan & Progres Penerimaan Gudang
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
                        <th class="py-3 px-3 text-right">Diterima Gudang</th>
                        <th class="py-3 px-3 text-right">Sisa Belum Tiba</th>
                        <th class="py-3 px-3 text-right">Biaya Satuan</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-300/60 font-mono">
                    @foreach ($purchaseOrder->items as $item)
                        @php
                            $remaining = max(0, (float)$item->qty_ordered - (float)$item->qty_received);
                        @endphp
                        <tr class="hover:bg-cream-100/60">
                            <td class="py-3 px-4 font-semibold text-cream-900 whitespace-nowrap">{{ $item->item->code }}</td>
                            <td class="py-3 px-4 font-sans font-medium text-cream-900">{{ $item->item->name }}</td>
                            <td class="py-3 px-3 text-cream-600 whitespace-nowrap">{{ $item->item->unit }}</td>
                            <td class="py-3 px-3 text-right text-cream-800">{{ number_format($item->qty_ordered, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-emerald-800 font-semibold">{{ number_format($item->qty_received, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right font-bold {{ $remaining > 0 ? 'text-amber-700' : 'text-cream-400' }}">
                                {{ number_format($remaining, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right text-cream-700">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-bold text-cream-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Related Goods Receipts (Nota Penerimaan Gudang) -->
    <div class="neo-card p-6 space-y-4">
        <h2 class="text-xs font-bold text-cream-900 uppercase font-mono tracking-wider">
            Riwayat Penerimaan Barang Masuk (Goods Receipt / GRN)
        </h2>

        @if($purchaseOrder->goodsReceipts->isEmpty())
            <p class="text-xs text-cream-500 py-2">Belum ada nota penerimaan barang yang dicatat dari supplier.</p>
        @else
            <div class="space-y-3">
                @foreach ($purchaseOrder->goodsReceipts as $grn)
                    <div class="neo-card-sm p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs bg-cream-100/60">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('goods-receipts.show', $grn->id) }}" class="font-mono font-bold text-forest hover:underline">
                                    {{ $grn->grn_no }}
                                </a>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-emerald-100 text-emerald-800">
                                    {{ $grn->status }}
                                </span>
                            </div>
                            <p class="text-cream-600 font-mono text-[11px]">
                                Tanggal Diterima: {{ $grn->receipt_date->format('d/m/Y') }} | No. DO Supplier: {{ $grn->supplier_do_no ?? '-' }}
                            </p>
                        </div>

                        <a href="{{ route('goods-receipts.show', $grn->id) }}" class="neo-btn px-3 py-1.5 text-xs text-cream-800 hover:border-forest/50">
                            Lihat Nota Penerimaan &rarr;
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
