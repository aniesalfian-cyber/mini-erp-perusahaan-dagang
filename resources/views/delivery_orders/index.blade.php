@extends('layouts.app')

@section('title', 'Logistik: Surat Jalan (DO)')

@section('content')
<div class="space-y-6" x-data="{
    createModalOpen: false,
    selectedOrderId: '',
    eligibleOrders: {{ json_encode($eligibleOrders) }},
    deliveryRows: [],
    driverName: '',
    vehicleNo: '',
    doDate: '{{ date('Y-m-d') }}',
    onOrderChange() {
        const order = this.eligibleOrders.find(o => o.id == this.selectedOrderId);
        if (order && order.items) {
            this.deliveryRows = order.items.map(item => {
                const remaining = Math.max(0, parseFloat(item.qty_ordered) - parseFloat(item.qty_shipped));
                return {
                    so_item_id: item.id,
                    item_name: item.item ? item.item.name : 'Barang',
                    unit: item.item ? item.item.unit : '',
                    remaining_qty: remaining,
                    qty_delivered: remaining,
                };
            }).filter(r => r.remaining_qty > 0);
        } else {
            this.deliveryRows = [];
        }
    }
}">

    <!-- Top Action Bar -->
    <div class="glass-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-white/90 bg-white/80 shadow-glass">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-cream-900 tracking-tight">Divisi Gudang & Logistik: Surat Jalan (DO)</h1>
            <p class="text-xs text-cream-600">
                Penerbitan Surat Jalan pengiriman bertahap (parsial) atau penuh atas pesanan penjualan yang aktif.
            </p>
        </div>

        @if(in_array(Auth::user()->role->code, ['ADMIN', 'LOGISTIK']))
            <button @click="createModalOpen = true" type="button" class="glass-btn-primary px-4 py-2.5 text-xs font-semibold flex items-center justify-center space-x-1.5 rounded-xl shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Terbitkan Surat Jalan Baru</span>
            </button>
        @endif
    </div>

    <!-- Filter Bar -->
    <div class="glass-card p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs border border-white/80 bg-white/75 shadow-glass-sm">
        <form method="GET" action="{{ route('delivery-orders.index') }}" class="flex-1 flex flex-col sm:flex-row items-center gap-2 w-full">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor DO, nomor armada, supir, atau pelanggan..."
                       class="glass-input w-full pl-9 pr-3 py-2 text-xs">
                <svg class="w-4 h-4 text-cream-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <select name="status" class="glass-input px-3 py-2 text-xs w-full sm:w-auto">
                    <option value="ALL">Semua Status Pengiriman</option>
                    <option value="PREPARING" {{ $status === 'PREPARING' ? 'selected' : '' }}>PREPARING (Sedang Dimuat)</option>
                    <option value="IN_TRANSIT" {{ $status === 'IN_TRANSIT' ? 'selected' : '' }}>IN_TRANSIT (Dalam Perjalanan)</option>
                    <option value="DELIVERED" {{ $status === 'DELIVERED' ? 'selected' : '' }}>DELIVERED (Terkirim & Diterima)</option>
                </select>

                <button type="submit" class="glass-btn px-4 py-2 font-medium text-cream-800 hover:border-forest/50">
                    Saring
                </button>
            </div>
        </form>
    </div>

    <!-- DO Table (Desktop) & Cards (Mobile) -->
    <div class="glass-card overflow-hidden border border-white/80 bg-white/80 shadow-glass">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/80 font-mono text-cream-600 uppercase text-[11px]">
                        <th class="py-3 px-4">No. Surat Jalan (DO)</th>
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">No. Pesanan (SO)</th>
                        <th class="py-3 px-4">Tujuan / Pelanggan</th>
                        <th class="py-3 px-3">Armada & Supir</th>
                        <th class="py-3 px-3">Petugas Gudang</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($deliveryOrders as $do)
                        <tr class="hover:bg-white/90 transition-colors">
                            <td class="py-3 px-4 font-mono font-semibold text-cream-900 whitespace-nowrap">
                                <a href="{{ route('delivery-orders.show', $do->id) }}" class="text-forest hover:underline">
                                    {{ $do->do_no }}
                                </a>
                            </td>
                            <td class="py-3 px-3 font-mono text-cream-600 whitespace-nowrap">
                                {{ $do->do_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-3 font-mono text-cream-800 whitespace-nowrap">
                                <a href="{{ route('sales-orders.show', $do->sales_order_id) }}" class="hover:underline text-cream-700">
                                    {{ $do->salesOrder->order_no ?? '-' }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-medium text-cream-900 min-w-[150px]">
                                {{ $do->customer->name }}
                            </td>
                            <td class="py-3 px-3 text-cream-700 whitespace-nowrap">
                                {{ $do->vehicle_no ?? '-' }} ({{ $do->driver_name ?? '-' }})
                            </td>
                            <td class="py-3 px-3 text-cream-600 whitespace-nowrap">
                                {{ $do->logisticsUser->name ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-semibold
                                    {{ $do->delivery_status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-800' : ($do->delivery_status === 'IN_TRANSIT' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $do->delivery_status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <a href="{{ route('delivery-orders.print', $do->id) }}" target="_blank" class="glass-btn p-1.5 text-slate-700 hover:text-forest" title="Cetak Surat Jalan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>
                                    <a href="{{ route('delivery-orders.show', $do->id) }}" class="glass-btn px-2.5 py-1 text-xs text-forest hover:border-forest/50 font-medium">
                                        Rincian &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-cream-500">
                                Belum ada Surat Jalan yang diterbitkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (Minimalist) -->
        <div class="block md:hidden divide-y divide-slate-100 font-sans">
            @forelse ($deliveryOrders as $do)
                <div class="p-4 space-y-3 hover:bg-white/60 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-mono text-xs font-bold text-cream-900">{{ $do->do_no }}</span>
                                <span class="text-[10px] text-cream-500 font-mono">{{ $do->do_date->format('d/m/Y') }}</span>
                            </div>
                            <h3 class="text-sm font-semibold text-cream-900 leading-snug mt-0.5">{{ $do->customer->name }}</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold whitespace-nowrap
                            {{ $do->delivery_status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-800' : ($do->delivery_status === 'IN_TRANSIT' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                            {{ $do->delivery_status }}
                        </span>
                    </div>

                    <div class="text-xs text-cream-700 space-y-1 bg-slate-50/80 p-2.5 rounded-xl border border-slate-100 font-mono">
                        <div class="flex items-center justify-between">
                            <span class="text-cream-500 font-sans">SO Asal:</span>
                            <span class="font-semibold text-cream-900">{{ $do->salesOrder->order_no ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-cream-500 font-sans">Supir & Armada:</span>
                            <span class="text-cream-900 font-sans">{{ $do->driver_name ?? 'Armada' }} ({{ $do->vehicle_no ?? 'B 9102 PJ' }})</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1 text-xs">
                        <span class="text-[11px] text-cream-500 truncate max-w-[150px]">
                            Gudang: {{ $do->logisticsUser->name ?? 'Staf' }}
                        </span>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('delivery-orders.print', $do->id) }}" target="_blank" class="glass-btn px-2.5 py-1.5 text-xs text-slate-700 flex items-center space-x-1 rounded-lg">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Cetak</span>
                            </a>
                            <a href="{{ route('delivery-orders.show', $do->id) }}" class="glass-btn px-3 py-1.5 text-xs font-medium text-forest rounded-lg">
                                Rincian &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-cream-500 text-xs">
                    Belum ada Surat Jalan yang diterbitkan.
                </div>
            @endforelse
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $deliveryOrders->links() }}
        </div>
    </div>

    <!-- MODAL PENERBITAN SURAT JALAN BARU (LUXURY GLASS) -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-md flex items-center justify-center p-4">
        <div @click.away="createModalOpen = false" class="glass-card bg-white/95 max-w-2xl w-full p-6 space-y-4 shadow-glass-lg rounded-3xl border border-white/90 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-cream-900">Penerbitan Surat Jalan (Delivery Order)</h3>
                    <p class="text-[11px] text-cream-500">Pilih pesanan aktif dan tentukan kuantitas yang dikirim saat ini</p>
                </div>
                <button @click="createModalOpen = false" class="glass-btn p-1.5 text-cream-500 hover:text-cream-900 rounded-xl">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('delivery-orders.store') }}" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Pilih Pesanan Penjualan (SO Aktif yang Belum Terpenuhi) *</label>
                    <select name="sales_order_id" x-model="selectedOrderId" @change="onOrderChange()" required class="glass-input w-full px-3 py-2 font-mono">
                        <option value="">-- Pilih Sales Order --</option>
                        <template x-for="order in eligibleOrders" :key="order.id">
                            <option :value="order.id" x-text="order.order_no + ' - ' + (order.customer ? order.customer.name : '') + ' (' + order.status + ')'"></option>
                        </template>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Tanggal Surat Jalan *</label>
                        <input type="date" name="do_date" x-model="doDate" required class="glass-input w-full px-3 py-2 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Nomor Polisi Armada</label>
                        <input type="text" name="vehicle_no" placeholder="Contoh: B 9281 TYN" class="glass-input w-full px-3 py-2 font-mono uppercase">
                    </div>
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Nama Supir / Pengemudi</label>
                        <input type="text" name="driver_name" placeholder="Nama supir pengantar" class="glass-input w-full px-3 py-2">
                    </div>
                </div>

                <!-- Items Delivery Allocation Table -->
                <div x-show="deliveryRows.length > 0" class="space-y-2 pt-2 border-t border-slate-100">
                    <label class="font-bold text-cream-900 uppercase font-mono tracking-wider text-[11px] block">
                        Alokasi Kuantitas Pengiriman (Bisa Parsial/Sebagian)
                    </label>

                    <div class="space-y-2">
                        <template x-for="(row, index) in deliveryRows" :key="index">
                            <div class="grid grid-cols-12 gap-2 items-center bg-slate-50/70 p-2.5 rounded-2xl border border-slate-200/60">
                                <input type="hidden" :name="'items[' + index + '][so_item_id]'" :value="row.so_item_id">

                                <div class="col-span-12 sm:col-span-6">
                                    <span class="font-semibold text-cream-900 block" x-text="row.item_name"></span>
                                    <span class="text-[10px] text-cream-500 font-mono" x-text="'Sisa Pesanan Belum Kirim: ' + row.remaining_qty + ' ' + row.unit"></span>
                                </div>

                                <div class="col-span-12 sm:col-span-6 flex items-center justify-end space-x-2">
                                    <label class="text-[11px] text-cream-600 whitespace-nowrap">Kirim Sekarang:</label>
                                    <input type="number" step="0.01" min="0" :max="row.remaining_qty"
                                           :name="'items[' + index + '][qty_delivered]'" x-model="row.qty_delivered"
                                           class="glass-input w-28 px-2 py-1.5 font-mono text-center font-bold text-forest text-sm">
                                    <span class="font-mono text-xs text-cream-700" x-text="row.unit"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Catatan Tambahan Surat Jalan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan instruksi bongkar muatan..." class="glass-input w-full px-3 py-2"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="createModalOpen = false" class="glass-btn px-4 py-2 font-medium text-cream-700">
                        Batal
                    </button>
                    <button type="submit" class="glass-btn-primary px-5 py-2 font-semibold" :disabled="deliveryRows.length === 0">
                        Terbitkan Surat Jalan & Potong Stok
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
