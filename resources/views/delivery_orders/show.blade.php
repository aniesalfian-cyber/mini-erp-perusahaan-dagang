@extends('layouts.app')

@section('title', 'Surat Jalan: ' . $deliveryOrder->do_no)

@section('content')
<div class="space-y-6" x-data="{ statusModalOpen: false }">

    <!-- Top Action & Breadcrumb -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-cream-500 mb-1">
                <a href="{{ route('delivery-orders.index') }}" class="hover:underline">Surat Jalan (DO)</a>
                <span>/</span>
                <span class="font-mono text-cream-800">{{ $deliveryOrder->do_no }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-lg sm:text-xl font-bold text-cream-900">{{ $deliveryOrder->do_no }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold
                    {{ $deliveryOrder->delivery_status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-800' : ($deliveryOrder->delivery_status === 'IN_TRANSIT' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                    {{ $deliveryOrder->delivery_status }}
                </span>
            </div>
            <p class="text-xs text-cream-600 mt-0.5">Diterbitkan pada {{ $deliveryOrder->do_date->format('d F Y') }} oleh {{ $deliveryOrder->logisticsUser->name ?? '-' }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if(in_array(Auth::user()->role->code, ['ADMIN', 'LOGISTIK']) && $deliveryOrder->delivery_status !== 'DELIVERED')
                <button @click="statusModalOpen = true" type="button" class="neo-btn-primary px-3.5 py-2 text-xs font-semibold rounded-xl flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Konfirmasi Sampai (Diterima)</span>
                </button>
            @endif

            @if(in_array(Auth::user()->role->code, ['ADMIN', 'ACCOUNTING']) && !$deliveryOrder->invoice)
                <form method="POST" action="{{ route('invoices.create-from-do') }}">
                    @csrf
                    <input type="hidden" name="delivery_order_id" value="{{ $deliveryOrder->id }}">
                    <button type="submit" class="neo-btn px-3.5 py-2 text-xs font-semibold text-purple-800 hover:bg-purple-50 rounded-xl flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Terbitkan Faktur (Invoice)</span>
                    </button>
                </form>
            @elseif($deliveryOrder->invoice)
                <a href="{{ route('invoices.show', $deliveryOrder->invoice->id) }}" class="neo-btn px-3 py-2 text-xs font-semibold text-forest hover:border-forest/50 rounded-xl">
                    Lihat Faktur ({{ $deliveryOrder->invoice->invoice_no }}) &rarr;
                </a>
            @endif

            <a href="{{ route('delivery-orders.index') }}" class="neo-btn px-3 py-2 text-xs font-medium text-cream-700 rounded-xl">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Logistics & Delivery Information -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Customer & Delivery Destination -->
        <div class="neo-card p-4 space-y-2">
            <span class="text-[11px] font-mono uppercase text-cream-500">Tujuan Pengiriman</span>
            <h3 class="text-sm font-bold text-cream-900">{{ $deliveryOrder->customer->name }}</h3>
            <p class="text-xs text-cream-600 font-mono">{{ $deliveryOrder->customer->code }} | Telp: {{ $deliveryOrder->customer->phone ?? '-' }}</p>
            <p class="text-xs text-cream-700">{{ $deliveryOrder->customer->address ?? '-' }}</p>
        </div>

        <!-- Armada & Driver -->
        <div class="neo-card p-4 space-y-2">
            <span class="text-[11px] font-mono uppercase text-cream-500">Armada & Supir Pengantar</span>
            <p class="text-base font-mono font-bold text-cream-900">{{ $deliveryOrder->vehicle_no ?? 'Belum ada nomor armada' }}</p>
            <p class="text-xs text-cream-700">Supir: <strong class="text-cream-900">{{ $deliveryOrder->driver_name ?? '-' }}</strong></p>
            <p class="text-[11px] text-cream-500">Petugas Gudang: {{ $deliveryOrder->logisticsUser->name ?? '-' }}</p>
        </div>

        <!-- Receipt Status -->
        <div class="neo-card p-4 space-y-2">
            <span class="text-[11px] font-mono uppercase text-cream-500">Bukti Penerimaan Barang</span>
            @if($deliveryOrder->delivery_status === 'DELIVERED')
                <p class="text-xs text-emerald-800 font-bold">Barang Diterima Pelanggan</p>
                <p class="text-xs text-cream-700">Penerima: <strong class="text-cream-900">{{ $deliveryOrder->recipient_name }}</strong></p>
                <p class="text-[11px] text-cream-500 font-mono">Waktu: {{ $deliveryOrder->delivered_at ? $deliveryOrder->delivered_at->format('d/m/Y H:i') : '-' }}</p>
            @else
                <p class="text-xs text-amber-700 font-medium">Dalam proses ekspedisi pengantaran...</p>
                <p class="text-[11px] text-cream-500">Menunggu konfirmasi tanda tangan penerima di lokasi proyek</p>
            @endif
        </div>
    </div>

    <!-- Items Delivered Table -->
    <div class="neo-card overflow-hidden">
        <div class="p-4 border-b border-cream-300 bg-cream-100/50 flex items-center justify-between">
            <h2 class="text-xs font-bold text-cream-900 uppercase font-mono tracking-wider">
                Daftar Barang yang Dimuat & Dikirim
            </h2>
            <span class="text-[11px] font-mono text-cream-500">Atas Pesanan: {{ $deliveryOrder->salesOrder->order_no ?? '-' }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-cream-300 text-cream-500 font-mono text-[11px]">
                        <th class="py-3 px-4">No.</th>
                        <th class="py-3 px-4">Kode SKU</th>
                        <th class="py-3 px-4">Nama Barang</th>
                        <th class="py-3 px-3">Satuan</th>
                        <th class="py-3 px-4 text-right">Kuantitas yang Dikirim</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-300/60 font-mono">
                    @foreach ($deliveryOrder->items as $idx => $item)
                        <tr class="hover:bg-cream-100/60">
                            <td class="py-3 px-4 text-cream-500">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-semibold text-cream-900 whitespace-nowrap">{{ $item->item->code }}</td>
                            <td class="py-3 px-4 font-sans font-medium text-cream-900">{{ $item->item->name }}</td>
                            <td class="py-3 px-3 text-cream-600 whitespace-nowrap">{{ $item->item->unit }}</td>
                            <td class="py-3 px-4 text-right font-bold text-forest text-sm">
                                {{ number_format($item->qty_delivered, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL KONFIRMASI STATUS PENGIRIMAN -->
    <div x-show="statusModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="statusModalOpen = false" class="neo-card bg-cream-100 max-w-md w-full p-6 space-y-4 shadow-neo-lg rounded-2xl border border-cream-300">
            <div class="flex items-center justify-between border-b border-cream-300/80 pb-3">
                <h3 class="text-sm font-bold text-cream-900">Konfirmasi Status Pengiriman</h3>
                <button @click="statusModalOpen = false" class="neo-btn p-1.5 text-cream-500 hover:text-cream-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('delivery-orders.status', $deliveryOrder->id) }}" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Status Pengiriman *</label>
                    <select name="delivery_status" required class="neo-input w-full px-3 py-2 font-mono">
                        <option value="IN_TRANSIT" {{ $deliveryOrder->delivery_status === 'IN_TRANSIT' ? 'selected' : '' }}>IN_TRANSIT (Dalam Perjalanan Armada)</option>
                        <option value="DELIVERED" selected>DELIVERED (Barang Telah Tiba & Diterima)</option>
                        <option value="RETURNED">RETURNED (Gagal Kirim / Retur Gudang)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Nama Penerima di Lokasi Proyek</label>
                    <input type="text" name="recipient_name" value="{{ $deliveryOrder->recipient_name ?? $deliveryOrder->customer->name }}" required
                           class="neo-input w-full px-3 py-2">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-cream-300/80">
                    <button type="button" @click="statusModalOpen = false" class="neo-btn px-4 py-2 font-medium text-cream-700">
                        Batal
                    </button>
                    <button type="submit" class="neo-btn-primary px-5 py-2 font-semibold">
                        Perbarui Status
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
