@extends('layouts.app')

@section('title', 'Keuangan: Faktur Penjualan (Invoice)')

@section('content')
<div class="space-y-6" x-data="{ createModalOpen: false, unbilledDOs: {{ json_encode($unbilledDOs) }} }">

    <!-- Top Action Bar -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-cream-900">Divisi Keuangan: Faktur Penjualan (Invoice)</h1>
            <p class="text-xs text-cream-600">
                Penerbitan faktur tagihan resmi berbasis Surat Jalan sah, cetak invoice PDF, rekonsiliasi piutang, dan pencatatan pembayaran.
            </p>
        </div>

        @if(in_array(Auth::user()->role->code, ['ADMIN', 'ACCOUNTING']))
            <button @click="createModalOpen = true" type="button" class="neo-btn-primary px-4 py-2.5 text-xs font-semibold flex items-center justify-center space-x-1.5 rounded-xl shadow-neo-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Terbitkan Faktur dari Surat Jalan (DO)</span>
            </button>
        @endif
    </div>

    <!-- Filter Bar -->
    <div class="neo-card p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <form method="GET" action="{{ route('invoices.index') }}" class="flex-1 flex flex-col sm:flex-row items-center gap-2 w-full">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor invoice atau nama pelanggan..."
                       class="neo-input w-full pl-9 pr-3 py-2 text-xs">
                <svg class="w-4 h-4 text-cream-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <select name="status" class="neo-input px-3 py-2 text-xs w-full sm:w-auto">
                    <option value="ALL">Semua Status Pembayaran</option>
                    <option value="UNPAID" {{ $status === 'UNPAID' ? 'selected' : '' }}>UNPAID (Belum Dibayar)</option>
                    <option value="PARTIAL" {{ $status === 'PARTIAL' ? 'selected' : '' }}>PARTIAL (Cicilan / Sebagian)</option>
                    <option value="PAID" {{ $status === 'PAID' ? 'selected' : '' }}>PAID (Lunas Penuh)</option>
                </select>

                <button type="submit" class="neo-btn px-4 py-2 font-medium text-cream-800 hover:border-forest/50">
                    Saring
                </button>
            </div>
        </form>
    </div>

    <!-- Invoices Table (Desktop) & Cards (Mobile) -->
    <div class="neo-card overflow-hidden">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-cream-300 bg-cream-100/60 font-mono text-cream-600 uppercase text-[11px]">
                        <th class="py-3 px-4">No. Faktur (INV)</th>
                        <th class="py-3 px-3">Tgl Faktur</th>
                        <th class="py-3 px-3">Jatuh Tempo</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-3">No. DO Sah</th>
                        <th class="py-3 px-3 text-right">Total Tagihan</th>
                        <th class="py-3 px-3 text-right">Sudah Dibayar</th>
                        <th class="py-3 px-3 text-right">Sisa Piutang</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-300/60 font-mono">
                    @forelse ($invoices as $inv)
                        @php
                            $rem = max(0, (float)$inv->total_amount - (float)$inv->paid_amount);
                            $isOverdue = $rem > 0 && Carbon\Carbon::today()->gt($inv->due_date);
                        @endphp
                        <tr class="hover:bg-cream-100/70 transition-colors {{ $isOverdue ? 'bg-rose-50/25' : '' }}">
                            <td class="py-3 px-4 font-semibold text-cream-900 whitespace-nowrap">
                                <a href="{{ route('invoices.show', $inv->id) }}" class="text-forest hover:underline">
                                    {{ $inv->invoice_no }}
                                </a>
                            </td>
                            <td class="py-3 px-3 text-cream-600 whitespace-nowrap">
                                {{ $inv->invoice_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap {{ $isOverdue ? 'text-rose-700 font-bold' : 'text-cream-600' }}">
                                {{ $inv->due_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4 font-sans font-medium text-cream-900 min-w-[140px]">
                                {{ $inv->customer->name }}
                            </td>
                            <td class="py-3 px-3 text-cream-700 whitespace-nowrap">
                                <a href="{{ route('delivery-orders.show', $inv->delivery_order_id) }}" class="hover:underline text-cream-700">
                                    {{ $inv->deliveryOrder->do_no ?? '-' }}
                                </a>
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-cream-900 whitespace-nowrap">
                                Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right text-emerald-800 font-semibold whitespace-nowrap">
                                Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold whitespace-nowrap {{ $rem > 0 ? 'text-purple-950' : 'text-cream-400' }}">
                                Rp {{ number_format($rem, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold
                                    {{ $inv->payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : ($inv->payment_status === 'PARTIAL' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $inv->payment_status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1">
                                    <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="neo-btn p-1.5 text-cream-700 hover:text-forest" title="Cetak Format Browser">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>
                                    <a href="{{ route('invoices.pdf', $inv->id) }}" target="_blank" class="neo-btn p-1.5 text-cream-700 hover:text-forest" title="Unduh PDF">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                                    </a>
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="neo-btn px-2 py-1 text-xs text-forest hover:border-forest/50 font-sans font-medium">
                                        Kelola &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-cream-500 font-sans">
                                Belum ada Faktur Penjualan yang diterbitkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (Minimalist) -->
        <div class="block md:hidden divide-y divide-cream-300/60 font-sans">
            @forelse ($invoices as $inv)
                @php
                    $rem = max(0, (float)$inv->total_amount - (float)$inv->paid_amount);
                    $isOverdue = $rem > 0 && Carbon\Carbon::today()->gt($inv->due_date);
                @endphp
                <div class="p-4 space-y-3 hover:bg-white/60 transition-colors {{ $isOverdue ? 'bg-rose-50/20' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-mono text-xs font-bold text-cream-900">{{ $inv->invoice_no }}</span>
                                <span class="text-[10px] text-cream-500 font-mono">{{ $inv->invoice_date->format('d/m/Y') }}</span>
                            </div>
                            <h3 class="text-sm font-semibold text-cream-900 leading-snug mt-0.5">{{ $inv->customer->name }}</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold whitespace-nowrap
                            {{ $inv->payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : ($inv->payment_status === 'PARTIAL' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') }}">
                            {{ $inv->payment_status }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 p-2.5 rounded-xl bg-slate-50/80 border border-slate-100 text-center font-mono text-xs">
                        <div>
                            <span class="text-[10px] text-cream-500 font-sans block">Total</span>
                            <span class="font-bold text-cream-900 text-xs">Rp {{ number_format($inv->total_amount / 1000000, 1) }}jt</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-cream-500 font-sans block">Dibayar</span>
                            <span class="font-semibold text-emerald-800 text-xs">Rp {{ number_format($inv->paid_amount / 1000000, 1) }}jt</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-cream-500 font-sans block">Sisa Piutang</span>
                            <span class="font-bold {{ $rem > 0 ? 'text-rose-700' : 'text-slate-400' }} text-xs">Rp {{ number_format($rem / 1000000, 1) }}jt</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1 text-xs">
                        <span class="text-[11px] {{ $isOverdue ? 'text-rose-700 font-bold' : 'text-cream-500' }} font-mono">
                            Jatuh tempo: {{ $inv->due_date->format('d/m/Y') }}
                        </span>
                        <div class="flex items-center space-x-1.5">
                            <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="neo-btn px-2 py-1 text-xs text-slate-700 flex items-center space-x-1 rounded-lg">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Cetak</span>
                            </a>
                            <a href="{{ route('invoices.show', $inv->id) }}" class="neo-btn px-2.5 py-1 text-xs font-medium text-forest rounded-lg">
                                Kelola &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-cream-500 text-xs font-sans">
                    Belum ada Faktur Penjualan yang diterbitkan.
                </div>
            @endforelse
        </div>

        <div class="p-4 border-t border-cream-300">
            {{ $invoices->links() }}
        </div>
    </div>

    <!-- MODAL PENERBITAN FAKTUR DARI DO -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-md flex items-center justify-center p-4">
        <div @click.away="createModalOpen = false" class="glass-card bg-white/95 max-w-lg w-full p-6 space-y-4 shadow-glass-lg rounded-2xl border border-white/80">
            <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-cream-900">Penerbitan Faktur Penjualan (Invoice)</h3>
                    <p class="text-[11px] text-cream-500">Pilih Surat Jalan yang sudah terkirim & belum difakturkan</p>
                </div>
                <button @click="createModalOpen = false" class="neo-btn p-1.5 text-cream-500 hover:text-cream-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('invoices.create-from-do') }}" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Pilih Surat Jalan (Delivery Order) *</label>
                    <select name="delivery_order_id" required class="neo-input w-full px-3 py-2 font-mono">
                        <option value="">-- Pilih Surat Jalan --</option>
                        <template x-for="d in unbilledDOs" :key="d.id">
                            <option :value="d.id" x-text="d.do_no + ' - ' + (d.customer ? d.customer.name : '') + ' (' + d.delivery_status + ')'"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Termin Pembayaran (Hari Jatuh Tempo)</label>
                    <select name="due_days" class="neo-input w-full px-3 py-2">
                        <option value="14">14 Hari Kalender</option>
                        <option value="30" selected>30 Hari Kalender (Standar)</option>
                        <option value="45">45 Hari Kalender</option>
                        <option value="60">60 Hari Kalender</option>
                    </select>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-cream-300/80">
                    <button type="button" @click="createModalOpen = false" class="neo-btn px-4 py-2 font-medium text-cream-700">
                        Batal
                    </button>
                    <button type="submit" class="neo-btn-primary px-5 py-2 font-semibold">
                        Terbitkan Faktur & Catat Piutang
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
