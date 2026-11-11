@extends('layouts.app')

@section('title', 'Nota Penerimaan: ' . $goodsReceipt->grn_no)

@section('content')
<div class="space-y-6">

    <!-- Top Action & Breadcrumb -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-cream-500 mb-1">
                <a href="{{ route('goods-receipts.index') }}" class="hover:underline">Penerimaan Gudang (GRN)</a>
                <span>/</span>
                <span class="font-mono text-cream-800">{{ $goodsReceipt->grn_no }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-lg sm:text-xl font-bold text-cream-900">{{ $goodsReceipt->grn_no }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold bg-emerald-100 text-emerald-800">
                    {{ $goodsReceipt->status }}
                </span>
            </div>
            <p class="text-xs text-cream-600 mt-0.5">Diterima pada {{ $goodsReceipt->receipt_date->format('d F Y') }} oleh {{ $goodsReceipt->logisticsUser->name ?? '-' }}</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('goods-receipts.print', $goodsReceipt->id) }}" target="_blank" class="neo-btn px-3.5 py-2 text-xs font-semibold text-slate-800 hover:bg-slate-100 rounded-xl flex items-center space-x-1.5 shadow-sm">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Nota Penerimaan (GRN)</span>
            </a>

            <a href="{{ route('purchase-orders.show', $goodsReceipt->purchase_order_id) }}" class="neo-btn px-3 py-2 text-xs font-semibold text-forest hover:border-forest/50 rounded-xl">
                Lihat PO Asal ({{ $goodsReceipt->purchaseOrder->po_no }}) &rarr;
            </a>

            <a href="{{ route('goods-receipts.index') }}" class="neo-btn px-3 py-2 text-xs font-medium text-cream-700 rounded-xl">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="neo-card p-4 space-y-2">
            <span class="text-[11px] font-mono uppercase text-cream-500">Pemasok Barang</span>
            <h3 class="text-sm font-bold text-cream-900">{{ $goodsReceipt->supplier->name }}</h3>
            <p class="text-xs text-cream-600 font-mono">{{ $goodsReceipt->supplier->code }} | Telp: {{ $goodsReceipt->supplier->phone ?? '-' }}</p>
            <p class="text-xs text-cream-700">No. Surat Jalan Pemasok: <strong class="font-mono text-cream-900">{{ $goodsReceipt->supplier_do_no ?? '-' }}</strong></p>
        </div>

        <div class="neo-card p-4 space-y-2">
            <span class="text-[11px] font-mono uppercase text-cream-500">Petugas Pemeriksa Gudang</span>
            <p class="text-sm font-bold text-cream-900">{{ $goodsReceipt->logisticsUser->name ?? '-' }}</p>
            <p class="text-xs text-cream-600">Inspeksi fisik lolos uji & mutasi stok berhasil dicatat ke saldo gudang.</p>
        </div>

        <div class="neo-card p-4 space-y-2">
            <span class="text-[11px] font-mono uppercase text-cream-500">Catatan Penerimaan</span>
            <p class="text-xs text-cream-700">{{ $goodsReceipt->notes ?? 'Tidak ada catatan tambahan saat pembongkaran.' }}</p>
        </div>
    </div>

    <!-- Items Received Table -->
    <div class="neo-card overflow-hidden">
        <div class="p-4 border-b border-cream-300 bg-cream-100/50 flex items-center justify-between">
            <h2 class="text-xs font-bold text-cream-900 uppercase font-mono tracking-wider">
                Hasil Pemeriksaan Barang Masuk
            </h2>
            <span class="text-[11px] font-mono text-cream-500">Referensi: {{ $goodsReceipt->purchaseOrder->po_no }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-cream-300 text-cream-500 font-mono text-[11px]">
                        <th class="py-3 px-4">No.</th>
                        <th class="py-3 px-4">Kode SKU</th>
                        <th class="py-3 px-4">Nama Barang</th>
                        <th class="py-3 px-3">Satuan</th>
                        <th class="py-3 px-3 text-right">Diterima Fisik</th>
                        <th class="py-3 px-3 text-right font-bold text-emerald-800">Lolos (Masuk Stok)</th>
                        <th class="py-3 px-3 text-right text-rose-700">Ditolak (Cacat)</th>
                        <th class="py-3 px-4">Alasan Penolakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-300/60 font-mono">
                    @foreach ($goodsReceipt->items as $idx => $item)
                        <tr class="hover:bg-cream-100/60">
                            <td class="py-3 px-4 text-cream-500">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-semibold text-cream-900 whitespace-nowrap">{{ $item->item->code }}</td>
                            <td class="py-3 px-4 font-sans font-medium text-cream-900">{{ $item->item->name }}</td>
                            <td class="py-3 px-3 text-cream-600 whitespace-nowrap">{{ $item->item->unit }}</td>
                            <td class="py-3 px-3 text-right text-cream-800">{{ number_format($item->qty_received, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right font-bold text-emerald-700 text-sm">
                                {{ number_format($item->qty_accepted, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-rose-700">
                                {{ number_format($item->qty_rejected, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-rose-700 font-sans max-w-xs truncate" title="{{ $item->reject_reason }}">
                                {{ $item->reject_reason ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
