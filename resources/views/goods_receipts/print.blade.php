@extends('layouts.print')

@section('title', 'Nota Penerimaan Barang - ' . $goodsReceipt->grn_no)

@section('content')
<div class="space-y-6">

    <!-- Document Title & Meta Box -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-mono uppercase tracking-widest text-slate-500 font-semibold block">Bukti Fisik Masuk Stok Gudang</span>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">NOTA PENERIMAAN BARANG (GRN)</h2>
        </div>
        <div class="text-left sm:text-right">
            <span class="text-xs text-slate-500 font-mono block">Nomor Penerimaan (GRN):</span>
            <span class="text-base sm:text-lg font-bold font-mono text-forest-800">{{ $goodsReceipt->grn_no }}</span>
            <div class="mt-1">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300">
                    STATUS: {{ $goodsReceipt->status }}
                </span>
            </div>
        </div>
    </div>

    <!-- Info Grid: Pemasok & Rincian Penerimaan -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1.5">
            <span class="text-[10px] font-mono uppercase text-slate-500 font-bold tracking-wider block">Pemasok / Pengirim Material</span>
            <h3 class="text-sm font-bold text-slate-900">{{ $goodsReceipt->supplier->name }}</h3>
            <p class="text-slate-600 font-mono">Kode: {{ $goodsReceipt->supplier->code }} | Telp: {{ $goodsReceipt->supplier->phone ?? 'Tidak ada data telepon' }}</p>
            <p class="text-slate-700 leading-relaxed">{{ $goodsReceipt->supplier->address ?? 'Alamat pemasok' }}</p>
            <div class="pt-2 border-t border-slate-200 text-slate-700">
                <span>No. Surat Jalan Pemasok:</span>
                <strong class="font-mono text-slate-900 ml-1">{{ $goodsReceipt->supplier_do_no ?? '(Tidak dicantumkan)' }}</strong>
            </div>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1.5">
            <span class="text-[10px] font-mono uppercase text-slate-500 font-bold tracking-wider block">Informasi Validasi & Referensi Order</span>
            <div class="grid grid-cols-2 gap-2 text-slate-700">
                <div>
                    <span class="text-slate-500 block text-[11px]">Tanggal Diterima:</span>
                    <strong class="font-mono text-slate-900">{{ $goodsReceipt->receipt_date->format('d F Y') }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">No. Purchase Order:</span>
                    <strong class="font-mono text-slate-900">{{ $goodsReceipt->purchaseOrder->po_no ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Petugas Penerima:</span>
                    <strong class="text-slate-900">{{ $goodsReceipt->logisticsUser->name ?? 'Staf Gudang' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Lokasi Bongkar:</span>
                    <strong class="text-slate-900 font-mono">Gudang Material Tangerang</strong>
                </div>
            </div>
            @if($goodsReceipt->notes)
                <div class="pt-2 border-t border-slate-200 mt-2 text-[11px] text-slate-600">
                    <strong>Catatan Pembongkaran:</strong> {{ $goodsReceipt->notes }}
                </div>
            @endif
        </div>
    </div>

    <!-- Items Inspection Table -->
    <div class="border border-slate-300 rounded-xl overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-forest-900 text-white font-mono uppercase text-[11px]">
                <tr>
                    <th class="py-2.5 px-3 text-center w-12">No.</th>
                    <th class="py-2.5 px-3">Kode SKU</th>
                    <th class="py-2.5 px-4">Nama Barang Material</th>
                    <th class="py-2.5 px-3 text-center">Satuan</th>
                    <th class="py-2.5 px-3 text-right">Diterima Fisik</th>
                    <th class="py-2.5 px-3 text-right font-bold text-emerald-300">Lolos (Masuk Stok)</th>
                    <th class="py-2.5 px-3 text-right text-rose-300">Ditolak (Cacat)</th>
                    <th class="py-2.5 px-4">Catatan Kualitas Fisik</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-mono">
                @php 
                    $totalTerima = 0; 
                    $totalLolos = 0; 
                    $totalTolak = 0; 
                @endphp
                @foreach ($goodsReceipt->items as $index => $item)
                    @php 
                        $totalTerima += (float)$item->qty_received; 
                        $totalLolos += (float)$item->qty_accepted; 
                        $totalTolak += (float)$item->qty_rejected; 
                    @endphp
                    <tr class="{{ $index % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }}">
                        <td class="py-2.5 px-3 text-center text-slate-500">{{ $index + 1 }}</td>
                        <td class="py-2.5 px-3 font-semibold text-slate-900">{{ $item->item->code }}</td>
                        <td class="py-2.5 px-4 font-sans font-medium text-slate-900">{{ $item->item->name }}</td>
                        <td class="py-2.5 px-3 text-center text-slate-600">{{ $item->item->unit }}</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-800">{{ number_format($item->qty_received, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right font-bold text-emerald-800 bg-emerald-50/50">{{ number_format($item->qty_accepted, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right font-bold {{ $item->qty_rejected > 0 ? 'text-rose-700 bg-rose-50/50' : 'text-slate-400' }}">
                            {{ number_format($item->qty_rejected, 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-4 font-sans text-slate-600 text-[11px]">
                            {{ $item->reject_reason ?? 'Kondisi kemasan rapi dan lolos sortir' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50 font-mono border-t-2 border-slate-300">
                <tr>
                    <td colspan="4" class="py-2.5 px-4 font-bold text-slate-800 text-right">RINGKASAN TOTAL FISIK:</td>
                    <td class="py-2.5 px-3 font-bold text-slate-900 text-right">{{ number_format($totalTerima, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 font-bold text-emerald-800 text-right bg-emerald-50/70">{{ number_format($totalLolos, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-3 font-bold text-rose-700 text-right bg-rose-50/70">{{ number_format($totalTolak, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-4 text-slate-500 text-[11px]">Unit Telah Dimutasi ke Sistem</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Official 3-Column Signatures Block -->
    <div class="signature-block pt-4">
        <div class="grid grid-cols-3 gap-4 text-center text-xs">
            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Pemeriksa & Penerima:</span>
                    <strong class="text-slate-900">Petugas Gudang</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-3/4 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">{{ $goodsReceipt->logisticsUser->name ?? '(................................)' }}</span>
                </div>
            </div>

            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Pengantar Barang:</span>
                    <strong class="text-slate-900">Supir / Ekspedisi Pemasok</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-3/4 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">( Ekspedisi Pemasok )</span>
                </div>
            </div>

            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Mengetahui & Menyetujui:</span>
                    <strong class="text-slate-900">Kepala Bagian Logistik</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-3/4 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">( Verifikasi Mutasi Stok )</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
