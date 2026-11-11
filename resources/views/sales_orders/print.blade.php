@extends('layouts.print')

@section('title', 'Surat Pesanan Penjualan - ' . $salesOrder->order_no)

@section('content')
<div class="space-y-6">

    <!-- Document Title & Meta Box -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-mono uppercase tracking-widest text-slate-500 font-semibold block">Dokumen Transaksi Penjualan</span>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">SURAT PESANAN PENJUALAN (SO)</h2>
        </div>
        <div class="text-left sm:text-right">
            <span class="text-xs text-slate-500 font-mono block">Nomor Pesanan:</span>
            <span class="text-base sm:text-lg font-bold font-mono text-forest-800">{{ $salesOrder->order_no }}</span>
            <div class="mt-1">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider
                    {{ $salesOrder->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($salesOrder->status === 'PARTIAL_SHIPPED' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-blue-100 text-blue-800 border border-blue-300') }}">
                    STATUS: {{ $salesOrder->status }}
                </span>
            </div>
        </div>
    </div>

    <!-- Info Grid: Pelanggan & Detail Pesanan -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1.5">
            <span class="text-[10px] font-mono uppercase text-slate-500 font-bold tracking-wider block">Pemesan (Customer)</span>
            <h3 class="text-sm font-bold text-slate-900">{{ $salesOrder->customer->name }}</h3>
            <p class="text-slate-600 font-mono">Kode: {{ $salesOrder->customer->code }} | Telp: {{ $salesOrder->customer->phone ?? 'Tidak ada data telepon' }}</p>
            <p class="text-slate-700 leading-relaxed">{{ $salesOrder->customer->address ?? 'Alamat proyek tidak dicantumkan' }}</p>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1.5">
            <span class="text-[10px] font-mono uppercase text-slate-500 font-bold tracking-wider block">Detail & Ketentuan Transaksi</span>
            <div class="grid grid-cols-2 gap-2 text-slate-700">
                <div>
                    <span class="text-slate-500 block text-[11px]">Tanggal Pemesanan:</span>
                    <strong class="font-mono text-slate-900">{{ $salesOrder->order_date->format('d F Y') }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Sales Officer:</span>
                    <strong class="text-slate-900">{{ $salesOrder->salesUser->name ?? 'Staf Marketing' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Syarat Pembayaran:</span>
                    <strong class="text-slate-900 font-mono">Tempo 30 Hari</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Plafon Kredit Mitra:</span>
                    <strong class="font-mono text-slate-900">Rp {{ number_format($salesOrder->customer->credit_limit, 0, ',', '.') }}</strong>
                </div>
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
                    <th class="py-2.5 px-4">Nama Material & Spesifikasi</th>
                    <th class="py-2.5 px-3 text-center">Satuan</th>
                    <th class="py-2.5 px-3 text-right">Qty Pesan</th>
                    <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                    <th class="py-2.5 px-4 text-right">Jumlah (Subtotal)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-mono">
                @php $totalQty = 0; @endphp
                @foreach ($salesOrder->items as $index => $item)
                    @php $totalQty += (float)$item->qty_ordered; @endphp
                    <tr class="{{ $index % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }}">
                        <td class="py-2.5 px-3 text-center text-slate-500">{{ $index + 1 }}</td>
                        <td class="py-2.5 px-3 font-semibold text-slate-900">{{ $item->item->code }}</td>
                        <td class="py-2.5 px-4 font-sans font-medium text-slate-900">{{ $item->item->name }}</td>
                        <td class="py-2.5 px-3 text-center text-slate-600">{{ $item->item->unit }}</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">{{ number_format($item->qty_ordered, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right text-slate-700">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
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
                "{{ App\Helpers\NumberHelper::terbilang($salesOrder->total_amount) }}"
            </p>
            @if($salesOrder->notes)
                <p class="text-[11px] text-slate-600"><strong>Catatan Pesanan:</strong> {{ $salesOrder->notes }}</p>
            @endif
        </div>

        <div class="sm:col-span-5 bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2 font-mono text-xs">
            <div class="flex items-center justify-between text-slate-600">
                <span>Total Kuantitas:</span>
                <span class="font-bold text-slate-900">{{ number_format($totalQty, 0, ',', '.') }} Unit</span>
            </div>
            <div class="flex items-center justify-between text-slate-600">
                <span>Subtotal Barang:</span>
                <span>Rp {{ number_format($salesOrder->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between text-slate-600">
                <span>PPN (Sudah Termasuk):</span>
                <span>Rp 0</span>
            </div>
            <div class="h-px bg-slate-300"></div>
            <div class="flex items-center justify-between text-sm font-bold text-forest-900 bg-emerald-50/70 p-2 rounded-lg border border-emerald-200">
                <span>TOTAL AKHIR:</span>
                <span>Rp {{ number_format($salesOrder->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Official Signatures Block -->
    <div class="signature-block pt-4">
        <div class="grid grid-cols-3 gap-4 text-center text-xs">
            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Dibuat & Dikonfirmasi:</span>
                    <strong class="text-slate-900">Sales / Marketing</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-3/4 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">{{ $salesOrder->salesUser->name ?? '(................................)' }}</span>
                </div>
            </div>

            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Disetujui Operasional:</span>
                    <strong class="text-slate-900">Kepala Gudang / Logistik</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-3/4 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">( Bagian Alokasi Stok )</span>
                </div>
            </div>

            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Pemesan Material:</span>
                    <strong class="text-slate-900">Pelanggan / Kontraktor</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-3/4 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">{{ $salesOrder->customer->name }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
