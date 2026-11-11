@extends('layouts.print')

@section('title', 'Faktur Penjualan - ' . $invoice->invoice_no)

@section('content')
<div class="space-y-6">

    <!-- Document Title & Meta Box -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-mono uppercase tracking-widest text-slate-500 font-semibold block">Dokumen Tagihan Finansial Resmi</span>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">FAKTUR PENJUALAN (INVOICE)</h2>
        </div>
        <div class="text-left sm:text-right">
            <span class="text-xs text-slate-500 font-mono block">Nomor Faktur:</span>
            <span class="text-base sm:text-lg font-bold font-mono text-forest-800">{{ $invoice->invoice_no }}</span>
            <div class="mt-1">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider
                    {{ $invoice->payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($invoice->payment_status === 'PARTIAL' ? 'bg-blue-100 text-blue-800 border border-blue-300' : 'bg-rose-100 text-rose-800 border border-rose-300') }}">
                    STATUS: {{ $invoice->payment_status }}
                </span>
            </div>
        </div>
    </div>

    <!-- Info Grid: Pelanggan & Detail Tagihan -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1.5">
            <span class="text-[10px] font-mono uppercase text-slate-500 font-bold tracking-wider block">Ditagihkan Kepada (Customer)</span>
            <h3 class="text-sm font-bold text-slate-900">{{ $invoice->customer->name }}</h3>
            <p class="text-slate-600 font-mono">Kode: {{ $invoice->customer->code }} | Telp: {{ $invoice->customer->phone ?? 'Tidak ada data telepon' }}</p>
            <p class="text-slate-700 leading-relaxed">{{ $invoice->customer->address ?? 'Alamat pelanggan' }}</p>
            <div class="pt-2 border-t border-slate-200 text-slate-700">
                <span>Alamat Proyek:</span>
                <span class="font-medium ml-1">{{ $invoice->customer->address ?? 'Lokasi Proyek' }}</span>
            </div>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1.5">
            <span class="text-[10px] font-mono uppercase text-slate-500 font-bold tracking-wider block">Ketentuan Tanggal & Dokumen Acuan</span>
            <div class="grid grid-cols-2 gap-2 text-slate-700">
                <div>
                    <span class="text-slate-500 block text-[11px]">Tanggal Faktur:</span>
                    <strong class="font-mono text-slate-900">{{ $invoice->invoice_date->format('d F Y') }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Jatuh Tempo:</span>
                    <strong class="font-mono text-rose-800">{{ $invoice->due_date->format('d F Y') }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">No. Surat Jalan (DO):</span>
                    <strong class="font-mono text-slate-900">{{ $invoice->deliveryOrder->do_no ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">No. Pesanan (SO):</span>
                    <strong class="font-mono text-slate-900">{{ $invoice->salesOrder->order_no ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Staf Keuangan:</span>
                    <strong class="text-slate-900">{{ $invoice->accountingUser->name ?? 'Bagian Piutang' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[11px]">Metode Pembayaran:</span>
                    <strong class="text-slate-900 font-mono">Transfer Bank / Bilyet Giro</strong>
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
                    <th class="py-2.5 px-3 text-right">Qty Terkirim</th>
                    <th class="py-2.5 px-3 text-right">Harga Satuan (Rp)</th>
                    <th class="py-2.5 px-4 text-right">Subtotal Tagihan (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-mono">
                @php $totalQty = 0; @endphp
                @foreach ($invoice->items as $index => $item)
                    @php $totalQty += (float)$item->qty; @endphp
                    <tr class="{{ $index % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }}">
                        <td class="py-2.5 px-3 text-center text-slate-500">{{ $index + 1 }}</td>
                        <td class="py-2.5 px-3 font-semibold text-slate-900">{{ $item->item->code }}</td>
                        <td class="py-2.5 px-4 font-sans font-medium text-slate-900">{{ $item->item->name }}</td>
                        <td class="py-2.5 px-3 text-center text-slate-600">{{ $item->item->unit }}</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">{{ number_format($item->qty, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right text-slate-700">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-4 text-right font-bold text-forest-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Financial Summary, Terbilang & Rekening Bank -->
    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-start">
        <div class="sm:col-span-7 space-y-3">
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1.5">
                <span class="text-[10px] font-mono uppercase text-slate-500 font-bold tracking-wider block">Nilai Terbilang:</span>
                <p class="text-xs font-semibold italic text-forest-900 leading-relaxed bg-white p-3 rounded-lg border border-slate-200">
                    "{{ App\Helpers\NumberHelper::terbilang($invoice->total_amount) }}"
                </p>
            </div>

            <!-- Rekening Pembayaran Resmi -->
            <div class="bg-emerald-50/60 border border-emerald-200 rounded-xl p-3.5 text-xs text-slate-700 space-y-1.5 font-mono">
                <strong class="font-sans text-[11px] font-bold text-forest-900 uppercase tracking-wide block">Instruksi Pembayaran Transfer:</strong>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                    <div class="bg-white p-2 rounded-lg border border-emerald-200">
                        <span class="text-slate-500 block">Bank Central Asia (BCA):</span>
                        <strong class="text-slate-900 text-xs">8830-192-888</strong><br>
                        <span class="text-[10px] text-slate-600">a.n. PT Prima Jaya Material</span>
                    </div>
                    <div class="bg-white p-2 rounded-lg border border-emerald-200">
                        <span class="text-slate-500 block">Bank Mandiri:</span>
                        <strong class="text-slate-900 text-xs">155-00-9821888-2</strong><br>
                        <span class="text-[10px] text-slate-600">a.n. PT Prima Jaya Material</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="sm:col-span-5 bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2 font-mono text-xs">
            <div class="flex items-center justify-between text-slate-600">
                <span>Total Barang:</span>
                <span class="font-bold text-slate-900">{{ number_format($totalQty, 0, ',', '.') }} Unit</span>
            </div>
            <div class="flex items-center justify-between text-slate-600">
                <span>Subtotal Faktur:</span>
                <span>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between text-slate-600">
                <span>PPN (11% Termasuk):</span>
                <span>Rp 0</span>
            </div>
            <div class="h-px bg-slate-300"></div>
            <div class="flex items-center justify-between text-sm font-bold text-slate-900">
                <span>TOTAL FAKTUR:</span>
                <span>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between text-slate-600 pt-1 border-t border-slate-200">
                <span>Sudah Dibayar:</span>
                <span class="text-emerald-700 font-bold">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between text-sm font-bold {{ $invoice->remaining_balance > 0 ? 'text-rose-700 bg-rose-50/80' : 'text-emerald-700 bg-emerald-50/80' }} p-2 rounded-lg border {{ $invoice->remaining_balance > 0 ? 'border-rose-200' : 'border-emerald-200' }}">
                <span>SISA PIUTANG:</span>
                <span>Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Official Signatures Block -->
    <div class="signature-block pt-4">
        <div class="grid grid-cols-3 gap-4 text-center text-xs">
            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Diterbitkan Bagian Keuangan:</span>
                    <strong class="text-slate-900">Accounting Department</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-3/4 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">{{ $invoice->accountingUser->name ?? '(................................)' }}</span>
                </div>
            </div>

            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Disetujui Otoritas Finansial:</span>
                    <strong class="text-slate-900">Finance Manager / Direktur</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-3/4 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">( Cap & Tanda Tangan )</span>
                </div>
            </div>

            <div class="space-y-16">
                <div>
                    <span class="text-slate-500 block text-[11px]">Tanda Terima Faktur:</span>
                    <strong class="text-slate-900">Pelanggan / Pembayar</strong>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-3/4 mx-auto"></div>
                    <span class="text-[11px] text-slate-700 block mt-1 font-medium">{{ $invoice->customer->name }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
