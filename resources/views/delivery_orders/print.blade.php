@extends('layouts.print')

@section('title', 'Surat Jalan Pengiriman - ' . $deliveryOrder->do_no)

@section('content')
<div class="space-y-6">

    <!-- Document Title & Meta Box -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-mono uppercase tracking-widest text-slate-500 font-semibold block">Dokumen Legal Distribusi Barang</span>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">SURAT JALAN PENGIRIMAN (DO)</h2>
        </div>
        <div class="text-left sm:text-right">
            <span class="text-xs text-slate-500 font-mono block">Nomor Surat Jalan:</span>
            <span class="text-base sm:text-lg font-bold font-mono text-forest-800">{{ $deliveryOrder->do_no }}</span>
            <div class="mt-1">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider
                    {{ $deliveryOrder->delivery_status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-blue-100 text-blue-800 border border-blue-300' }}">
                    STATUS: {{ $deliveryOrder->delivery_status }}
                </span>
            </div>
        </div>
    </div>

    <!-- Info Grid: Penerima & Rincian Pengiriman -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1.5">
            <span class="text-[10px] font-mono uppercase text-slate-500 font-bold tracking-wider block">Tujuan Pengiriman & Penerima</span>
            <h3 class="text-sm font-bold text-slate-900">{{ $deliveryOrder->customer->name }}</h3>
            <p class="text-slate-600 font-mono">Kode Mitra: {{ $deliveryOrder->customer->code }} | Telp: {{ $deliveryOrder->customer->phone ?? 'Tidak ada data telepon' }}</p>
            <p class="text-slate-700 leading-relaxed font-medium">Alamat Bongkar Muat / Proyek:</p>
            <p class="text-slate-600 leading-relaxed bg-white p-2.5 rounded-lg border border-slate-200">
                {{ $deliveryOrder->customer->address ?? 'Alamat proyek utama' }}
            </p>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1.5">
            <span class="text-[10px] font-mono uppercase text-slate-500 font-bold tracking-wider block">Informasi Armada & Dokumen Referensi</span>
            <div class="grid grid-cols-2 gap-2 text-slate-700">
                <div>
                    <span class="text-slate-500 block text-[11px]">Tanggal Pengiriman:</span>
                    <strong class="font-mono text-slate-900">{{ $deliveryOrder->do_date->format('d F Y') }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">No. Pesanan Penjualan (SO):</span>
                    <strong class="font-mono text-slate-900">{{ $deliveryOrder->salesOrder->order_no ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Nama Pengemudi / Supir:</span>
                    <strong class="text-slate-900">{{ $deliveryOrder->driver_name ?? 'Armada Reguler Gudang' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Nomor Plat Kendaraan:</span>
                    <strong class="font-mono text-slate-900 uppercase">{{ $deliveryOrder->vehicle_no ?? 'B 9102 PJ' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Petugas Logistik:</span>
                    <strong class="text-slate-900">{{ $deliveryOrder->logisticsUser->name ?? 'Staf Gudang' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Penerima di Lokasi:</span>
                    <strong class="text-slate-900">{{ $deliveryOrder->recipient_name ?? '(Belum Dikonfirmasi)' }}</strong>
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
                    <th class="py-2.5 px-4">Deskripsi Material Bangunan</th>
                    <th class="py-2.5 px-3 text-center">Satuan</th>
                    <th class="py-2.5 px-3 text-right">Qty Pesanan</th>
                    <th class="py-2.5 px-3 text-right font-bold">Qty Kirim Saat Ini</th>
                    <th class="py-2.5 px-4">Keterangan / Kondisi Fisik</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-mono">
                @php $totalKirim = 0; @endphp
                @foreach ($deliveryOrder->items as $index => $item)
                    @php $totalKirim += (float)$item->qty_delivered; @endphp
                    <tr class="{{ $index % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }}">
                        <td class="py-2.5 px-3 text-center text-slate-500">{{ $index + 1 }}</td>
                        <td class="py-2.5 px-3 font-semibold text-slate-900">{{ $item->item->code }}</td>
                        <td class="py-2.5 px-4 font-sans font-medium text-slate-900">{{ $item->item->name }}</td>
                        <td class="py-2.5 px-3 text-center text-slate-600">{{ $item->item->unit }}</td>
                        <td class="py-2.5 px-3 text-right text-slate-500">
                            {{ number_format($item->salesOrderItem->qty_ordered ?? $item->qty_delivered, 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-bold text-forest-900 text-sm">
                            {{ number_format($item->qty_delivered, 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-4 font-sans text-slate-600">
                            {{ $item->notes ?? 'Kemasan utuh, standar pabrik' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50 font-mono border-t-2 border-slate-300">
                <tr>
                    <td colspan="5" class="py-2.5 px-4 font-bold text-slate-800 text-right">TOTAL MATERIAL DIKIRIM:</td>
                    <td class="py-2.5 px-3 font-bold text-forest-900 text-right text-sm">{{ number_format($totalKirim, 0, ',', '.') }}</td>
                    <td class="py-2.5 px-4 text-slate-500 text-[11px]">Unit / Koli Siap Muat</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Catatan & Ketentuan Legal Pengiriman -->
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-[11px] text-slate-600 space-y-1">
        <strong class="text-slate-900 uppercase font-mono block">Ketentuan Penerimaan Barang:</strong>
        <p>1. Surat Jalan ini merupakan bukti penyerahan barang yang sah dari PT Prima Jaya Material kepada pihak pemesan.</p>
        <p>2. Barang wajib diinspeksi secara bersama pada saat pembongkaran. Klaim kekurangan atau kerusakan material tidak dilayani setelah Surat Jalan ditandatangani.</p>
        <p>3. Surat Jalan asli lembar putih kembali ke bagian Administrasi & Keuangan sebagai acuan penerbitan Faktur Penjualan (Invoice).</p>
    </div>

    <!-- Official 4-Column Signatures Block -->
    <div class="signature-block pt-4">
        <div class="grid grid-cols-4 gap-3 text-center text-xs">
            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Pengirim / Gudang:</span>
                    <strong class="text-slate-900">Petugas Logistik</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-4/5 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">{{ $deliveryOrder->logisticsUser->name ?? '(................................)' }}</span>
                </div>
            </div>

            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Pengemudi / Supir:</span>
                    <strong class="text-slate-900">Armada Angkutan</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-4/5 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">{{ $deliveryOrder->driver_name ?? '(................................)' }}</span>
                </div>
            </div>

            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Pemeriksa Gerbang:</span>
                    <strong class="text-slate-900">Security Pos Keluar</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-4/5 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">( Pos Gudang Tangerang )</span>
                </div>
            </div>

            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Penerima Barang Proyek:</span>
                    <strong class="text-slate-900">Pelanggan / Mandor</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-4/5 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">{{ $deliveryOrder->recipient_name ?? $deliveryOrder->customer->name }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
