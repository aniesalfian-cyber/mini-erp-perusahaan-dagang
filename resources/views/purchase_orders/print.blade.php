@extends('layouts.print')

@section('title', 'Purchase Order - ' . $purchaseOrder->po_no)

@section('content')
<div class="space-y-6">

    <!-- Document Title & Meta Box -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-mono uppercase tracking-widest text-slate-500 font-semibold block">Dokumen Resmi Pengadaan Material</span>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">SURAT PESANAN PEMBELIAN (PO)</h2>
        </div>
        <div class="text-left sm:text-right">
            <span class="text-xs text-slate-500 font-mono block">Nomor Purchase Order:</span>
            <span class="text-base sm:text-lg font-bold font-mono text-forest-800">{{ $purchaseOrder->po_no }}</span>
            <div class="mt-1">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider
                    {{ $purchaseOrder->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($purchaseOrder->status === 'PARTIAL_RECEIVED' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-blue-100 text-blue-800 border border-blue-300') }}">
                    STATUS: {{ $purchaseOrder->status }}
                </span>
            </div>
        </div>
    </div>

    <!-- Info Grid: Pemasok & Ketentuan Order -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1.5">
            <span class="text-[10px] font-mono uppercase text-slate-500 font-bold tracking-wider block">Ditujukan Kepada Pemasok (Supplier)</span>
            <h3 class="text-sm font-bold text-slate-900">{{ $purchaseOrder->supplier->name }}</h3>
            <p class="text-slate-600 font-mono">Kode Pemasok: {{ $purchaseOrder->supplier->code }}</p>
            <p class="text-slate-600">Kontak Person (PIC): <strong class="text-slate-900">{{ $purchaseOrder->supplier->contact_person ?? 'Bagian Penjualan Pabrik' }}</strong></p>
            <p class="text-slate-600 font-mono">Telepon: {{ $purchaseOrder->supplier->phone ?? 'Tidak ada data telepon' }}</p>
            <p class="text-slate-700 leading-relaxed">{{ $purchaseOrder->supplier->address ?? 'Alamat pabrik pemasok' }}</p>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1.5">
            <span class="text-[10px] font-mono uppercase text-slate-500 font-bold tracking-wider block">Ketentuan Pemesanan & Alamat Bongkar</span>
            <div class="grid grid-cols-2 gap-2 text-slate-700">
                <div>
                    <span class="text-slate-500 block text-[11px]">Tanggal Penerbitan PO:</span>
                    <strong class="font-mono text-slate-900">{{ $purchaseOrder->po_date->format('d F Y') }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Estimasi Tiba di Gudang:</span>
                    <strong class="font-mono text-slate-900">{{ $purchaseOrder->expected_date ? $purchaseOrder->expected_date->format('d F Y') : 'Sesuai Jadwal Pabrik' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Petugas Purchasing:</span>
                    <strong class="text-slate-900">{{ $purchaseOrder->purchasingUser->name ?? 'Staf Pengadaan' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Tujuan Penerimaan:</span>
                    <strong class="text-slate-900 font-mono">Gudang Utama Tangerang</strong>
                </div>
            </div>
            <div class="pt-2 border-t border-slate-200 mt-2 text-[11px] text-slate-600">
                <strong>Catatan PO:</strong> {{ $purchaseOrder->notes ?? 'Pengiriman harap disertai Nota / Surat Jalan pabrikan asli.' }}
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="border border-slate-300 rounded-xl overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-forest-900 text-white font-mono uppercase text-[11px]">
                <tr>
                    <th class="py-2.5 px-3 text-center w-12">No.</th>
                    <th class="py-2.5 px-3">Kode SKU</th>
                    <th class="py-2.5 px-4">Nama Barang & Spesifikasi Material</th>
                    <th class="py-2.5 px-3 text-center">Satuan</th>
                    <th class="py-2.5 px-3 text-right">Qty Order</th>
                    <th class="py-2.5 px-3 text-right">Harga Beli Pabrik</th>
                    <th class="py-2.5 px-4 text-right">Total Biaya (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-mono">
                @php $totalQty = 0; @endphp
                @foreach ($purchaseOrder->items as $index => $item)
                    @php $totalQty += (float)$item->qty_ordered; @endphp
                    <tr class="{{ $index % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }}">
                        <td class="py-2.5 px-3 text-center text-slate-500">{{ $index + 1 }}</td>
                        <td class="py-2.5 px-3 font-semibold text-slate-900">{{ $item->item->code }}</td>
                        <td class="py-2.5 px-4 font-sans font-medium text-slate-900">{{ $item->item->name }}</td>
                        <td class="py-2.5 px-3 text-center text-slate-600">{{ $item->item->unit }}</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">{{ number_format($item->qty_ordered, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right text-slate-700">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-4 text-right font-bold text-forest-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Financial Summary & Terbilang -->
    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-start">
        <div class="sm:col-span-7 bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2">
            <span class="text-[10px] font-mono uppercase text-slate-500 font-bold tracking-wider block">Nilai Terbilang:</span>
            <p class="text-xs font-semibold italic text-forest-900 leading-relaxed bg-white p-3 rounded-lg border border-slate-200">
                "{{ App\Helpers\NumberHelper::terbilang($purchaseOrder->total_amount) }}"
            </p>
            <p class="text-[11px] text-slate-500">
                Semua barang yang dikirimkan wajib memenuhi standar mutu pabrik dan lolos uji fisik gudang.
            </p>
        </div>

        <div class="sm:col-span-5 bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2 font-mono text-xs">
            <div class="flex items-center justify-between text-slate-600">
                <span>Total Item Dipesan:</span>
                <span class="font-bold text-slate-900">{{ number_format($totalQty, 0, ',', '.') }} Unit</span>
            </div>
            <div class="flex items-center justify-between text-slate-600">
                <span>Total Biaya Pengadaan:</span>
                <span>Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="h-px bg-slate-300"></div>
            <div class="flex items-center justify-between text-sm font-bold text-forest-900 bg-emerald-50/70 p-2 rounded-lg border border-emerald-200">
                <span>TOTAL PURCHASE ORDER:</span>
                <span>Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Official Signatures Block -->
    <div class="signature-block pt-4">
        <div class="grid grid-cols-3 gap-4 text-center text-xs">
            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Dibuat Bagian Pengadaan:</span>
                    <strong class="text-slate-900">Purchasing Department</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-3/4 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">{{ $purchaseOrder->purchasingUser->name ?? '(................................)' }}</span>
                </div>
            </div>

            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Disetujui Anggaran:</span>
                    <strong class="text-slate-900">Direktur / Owner</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-3/4 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">( Otoritas Finansial )</span>
                </div>
            </div>

            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Konfirmasi Kesanggupan:</span>
                    <strong class="text-slate-900">Pemasok / Pabrikan</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-3/4 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">{{ $purchaseOrder->supplier->name }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
