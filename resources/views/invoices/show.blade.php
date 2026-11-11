@extends('layouts.app')

@section('title', 'Faktur: ' . $invoice->invoice_no)

@section('content')
<div class="space-y-6" x-data="{ paymentModalOpen: false }">

    <!-- Top Action & Breadcrumb -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-cream-500 mb-1">
                <a href="{{ route('invoices.index') }}" class="hover:underline">Faktur Penjualan</a>
                <span>/</span>
                <span class="font-mono text-cream-800">{{ $invoice->invoice_no }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-lg sm:text-xl font-bold text-cream-900">{{ $invoice->invoice_no }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold
                    {{ $invoice->payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : ($invoice->payment_status === 'PARTIAL' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') }}">
                    {{ $invoice->payment_status }}
                </span>
            </div>
            <p class="text-xs text-cream-600 mt-0.5">Diterbitkan pada {{ $invoice->invoice_date->format('d F Y') }} oleh {{ $invoice->accountingUser->name ?? '-' }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('invoices.pdf', $invoice->id) }}" target="_blank" class="neo-btn-primary px-3.5 py-2 text-xs font-semibold rounded-xl flex items-center space-x-1.5 shadow-neo-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak / Unduh PDF</span>
            </a>

            @if(in_array(Auth::user()->role->code, ['ADMIN', 'ACCOUNTING']) && $invoice->payment_status !== 'PAID')
                <button @click="paymentModalOpen = true" type="button" class="neo-btn px-3.5 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-50 rounded-xl flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Catat Pembayaran Masuk</span>
                </button>
            @endif

            <a href="{{ route('invoices.index') }}" class="neo-btn px-3 py-2 text-xs font-medium text-cream-700 rounded-xl">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Invoice Header & Financial Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Customer & Due Date -->
        <div class="neo-card p-4 space-y-2">
            <span class="text-[11px] font-mono uppercase text-cream-500">Ditagihkan Kepada</span>
            <h3 class="text-sm font-bold text-cream-900">{{ $invoice->customer->name }}</h3>
            <p class="text-xs text-cream-600 font-mono">{{ $invoice->customer->code }} | Telp: {{ $invoice->customer->phone ?? '-' }}</p>
            <p class="text-xs text-cream-700">{{ $invoice->customer->address ?? '-' }}</p>
            <p class="text-xs text-cream-600 pt-1 border-t border-cream-300/60">
                Jatuh Tempo: <strong class="font-mono {{ Carbon\Carbon::today()->gt($invoice->due_date) && $invoice->remaining_balance > 0 ? 'text-rose-700' : 'text-cream-900' }}">{{ $invoice->due_date->format('d F Y') }}</strong>
            </p>
        </div>

        <!-- Reference Documents -->
        <div class="neo-card p-4 space-y-2">
            <span class="text-[11px] font-mono uppercase text-cream-500">Dokumen Acuan Sah</span>
            <p class="text-xs text-cream-700">Surat Jalan:
                <a href="{{ route('delivery-orders.show', $invoice->delivery_order_id) }}" class="font-mono font-bold text-forest hover:underline">
                    {{ $invoice->deliveryOrder->do_no ?? '-' }}
                </a>
            </p>
            <p class="text-xs text-cream-700">Sales Order:
                <a href="{{ route('sales-orders.show', $invoice->sales_order_id) }}" class="font-mono font-bold text-forest hover:underline">
                    {{ $invoice->salesOrder->order_no ?? '-' }}
                </a>
            </p>
            <p class="text-[11px] text-cream-500 font-sans">Petugas Accounting: {{ $invoice->accountingUser->name ?? '-' }}</p>
        </div>

        <!-- Balance Due Card -->
        <div class="neo-card p-4 space-y-2 border-l-4 {{ $invoice->remaining_balance > 0 ? 'border-l-purple-700' : 'border-l-emerald-600' }}">
            <span class="text-[11px] font-mono uppercase text-cream-500">Sisa Tagihan yang Harus Dibayar</span>
            <p class="text-2xl font-mono font-bold {{ $invoice->remaining_balance > 0 ? 'text-purple-950' : 'text-emerald-700' }}">
                Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}
            </p>
            <div class="flex items-center justify-between text-xs font-mono text-cream-600 pt-1">
                <span>Total: Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                <span class="text-emerald-700 font-semibold">Lunas: Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Items Invoiced Table -->
    <div class="neo-card overflow-hidden">
        <div class="p-4 border-b border-cream-300 bg-cream-100/50">
            <h2 class="text-xs font-bold text-cream-900 uppercase font-mono tracking-wider">
                Rincian Barang yang Ditagihkan
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-cream-300 text-cream-500 font-mono text-[11px]">
                        <th class="py-3 px-4">No.</th>
                        <th class="py-3 px-4">Deskripsi Barang</th>
                        <th class="py-3 px-3 text-right">Kuantitas</th>
                        <th class="py-3 px-3 text-right">Harga Satuan</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-300/60 font-mono">
                    @foreach ($invoice->items as $idx => $item)
                        <tr class="hover:bg-cream-100/60">
                            <td class="py-3 px-4 text-cream-500">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-sans font-medium text-cream-900">{{ $item->description }}</td>
                            <td class="py-3 px-3 text-right text-cream-800">{{ number_format($item->qty, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-cream-700">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-bold text-cream-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-cream-100/60 font-mono border-t border-cream-300">
                        <td colspan="4" class="py-3 px-4 text-right font-bold text-cream-800">TOTAL NILAI FAKTUR:</td>
                        <td class="py-3 px-4 text-right font-bold text-forest text-sm">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Payments History -->
    <div class="neo-card p-6 space-y-4">
        <h2 class="text-xs font-bold text-cream-900 uppercase font-mono tracking-wider">
            Riwayat Pembayaran & Pelunasan Faktur
        </h2>

        @if($invoice->payments->isEmpty())
            <p class="text-xs text-cream-500 py-2">Belum ada riwayat pembayaran yang tercatat untuk faktur ini.</p>
        @else
            <div class="space-y-3">
                @foreach ($invoice->payments as $pay)
                    <div class="neo-card-sm p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs bg-cream-100/60">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-mono font-bold text-emerald-800">{{ $pay->payment_no }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-cream-300 text-cream-800">
                                    {{ $pay->payment_method }}
                                </span>
                            </div>
                            <p class="text-cream-600 font-mono text-[11px]">
                                Tanggal: {{ $pay->payment_date->format('d/m/Y') }} | Ref/No. Bukti: {{ $pay->reference_no ?? '-' }} (Petugas: {{ $pay->accountingUser->name ?? '-' }})
                            </p>
                            @if($pay->notes)
                                <p class="text-cream-500 italic text-[11px]">{{ $pay->notes }}</p>
                            @endif
                        </div>

                        <div class="text-right">
                            <span class="text-[11px] text-cream-500 block">Jumlah Dibayar:</span>
                            <strong class="text-sm font-mono text-emerald-800">Rp {{ number_format($pay->amount, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- MODAL CATAT PEMBAYARAN MASUK -->
    <div x-show="paymentModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="paymentModalOpen = false" class="neo-card bg-cream-100 max-w-md w-full p-6 space-y-4 shadow-neo-lg rounded-2xl border border-cream-300">
            <div class="flex items-center justify-between border-b border-cream-300/80 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-cream-900">Catat Pembayaran Masuk</h3>
                    <p class="text-[11px] text-cream-500 font-mono">{{ $invoice->invoice_no }}</p>
                </div>
                <button @click="paymentModalOpen = false" class="neo-btn p-1.5 text-cream-500 hover:text-cream-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('invoices.payment', $invoice->id) }}" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Sisa Tagihan Faktur</label>
                    <input type="text" value="Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}" disabled
                           class="neo-input w-full px-3 py-2 bg-cream-200/50 font-mono font-bold text-purple-900">
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Nominal Pembayaran Diterima (Rp) *</label>
                    <input type="number" step="0.01" min="1" max="{{ $invoice->remaining_balance }}" name="amount" value="{{ $invoice->remaining_balance }}" required
                           class="neo-input w-full px-3 py-2 font-mono font-bold text-forest text-sm">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Metode Bayar *</label>
                        <select name="payment_method" required class="neo-input w-full px-3 py-2">
                            <option value="BANK_TRANSFER" selected>Transfer Bank</option>
                            <option value="CASH">Tunai (Kasir)</option>
                            <option value="GIRO">Bilyet Giro / Cek</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-cream-700 mb-1">No. Bukti / Rekening</label>
                        <input type="text" name="reference_no" placeholder="Contoh: BCA-9921" class="neo-input w-full px-3 py-2 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Keterangan Tambahan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan rekonsiliasi transfer..." class="neo-input w-full px-3 py-2"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-cream-300/80">
                    <button type="button" @click="paymentModalOpen = false" class="neo-btn px-4 py-2 font-medium text-cream-700">
                        Batal
                    </button>
                    <button type="submit" class="neo-btn-primary px-5 py-2 font-semibold">
                        Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
