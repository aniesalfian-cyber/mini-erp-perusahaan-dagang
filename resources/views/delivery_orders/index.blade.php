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
                    qty_delivered: remaining, // default kirim sisa penuh
                };
            }).filter(r => r.remaining_qty > 0);
        } else {
            this.deliveryRows = [];
        }
    }
}">

    <!-- Top Action Bar -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-cream-900">Divisi Gudang & Logistik: Surat Jalan (DO)</h1>
            <p class="text-xs text-cream-600">
                Penerbitan Surat Jalan pengiriman bertahap (parsial) atau penuh atas pesanan penjualan yang aktif.
            </p>
        </div>

        @if(in_array(Auth::user()->role->code, ['ADMIN', 'LOGISTIK']))
            <button @click="createModalOpen = true" type="button" class="neo-btn-primary px-4 py-2.5 text-xs font-semibold flex items-center justify-center space-x-1.5 rounded-xl shadow-neo-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Terbitkan Surat Jalan Baru</span>
            </button>
        @endif
    </div>

    <!-- Filter Bar -->
    <div class="neo-card p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <form method="GET" action="{{ route('delivery-orders.index') }}" class="flex-1 flex flex-col sm:flex-row items-center gap-2 w-full">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor DO, nomor armada, supir, atau pelanggan..."
                       class="neo-input w-full pl-9 pr-3 py-2 text-xs">
                <svg class="w-4 h-4 text-cream-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <select name="status" class="neo-input px-3 py-2 text-xs w-full sm:w-auto">
                    <option value="ALL">Semua Status Pengiriman</option>
                    <option value="PREPARING" {{ $status === 'PREPARING' ? 'selected' : '' }}>PREPARING (Sedang Dimuat)</option>
                    <option value="IN_TRANSIT" {{ $status === 'IN_TRANSIT' ? 'selected' : '' }}>IN_TRANSIT (Dalam Perjalanan)</option>
                    <option value="DELIVERED" {{ $status === 'DELIVERED' ? 'selected' : '' }}>DELIVERED (Terkirim & Diterima)</option>
                </select>

                <button type="submit" class="neo-btn px-4 py-2 font-medium text-cream-800 hover:border-forest/50">
                    Saring
                </button>
            </div>
        </form>
    </div>

    <!-- DO Table -->
    <div class="neo-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-cream-300 bg-cream-100/60 font-mono text-cream-600 uppercase text-[11px]">
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
                <tbody class="divide-y divide-cream-300/60">
                    @forelse ($deliveryOrders as $do)
                        <tr class="hover:bg-cream-100/70 transition-colors">
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
                                <a href="{{ route('delivery-orders.show', $do->id) }}" class="neo-btn px-2.5 py-1 text-xs text-forest hover:border-forest/50 font-medium">
                                    Rincian &rarr;
                                </a>
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

        <div class="p-4 border-t border-cream-300">
            {{ $deliveryOrders->links() }}
        </div>
    </div>

    <!-- MODAL PENERBITAN SURAT JALAN BARU -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="createModalOpen = false" class="neo-card bg-cream-100 max-w-2xl w-full p-6 space-y-4 shadow-neo-lg rounded-2xl border border-cream-300 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-cream-300/80 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-cream-900">Penerbitan Surat Jalan (Delivery Order)</h3>
                    <p class="text-[11px] text-cream-500">Pilih pesanan aktif dan tentukan kuantitas yang dikirim saat ini</p>
                </div>
                <button @click="createModalOpen = false" class="neo-btn p-1.5 text-cream-500 hover:text-cream-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('delivery-orders.store') }}" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Pilih Pesanan Penjualan (SO Aktif yang Belum Terpenuhi) *</label>
                    <select name="sales_order_id" x-model="selectedOrderId" @change="onOrderChange()" required class="neo-input w-full px-3 py-2 font-mono">
                        <option value="">-- Pilih Sales Order --</option>
                        <template x-for="order in eligibleOrders" :key="order.id">
                            <option :value="order.id" x-text="order.order_no + ' - ' + (order.customer ? order.customer.name : '') + ' (' + order.status + ')'"></option>
                        </template>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Tanggal Surat Jalan *</label>
                        <input type="date" name="do_date" x-model="doDate" required class="neo-input w-full px-3 py-2 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Nomor Polisi Armada</label>
                        <input type="text" name="vehicle_no" placeholder="Contoh: B 9281 TYN" class="neo-input w-full px-3 py-2 font-mono uppercase">
                    </div>
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Nama Supir / Pengemudi</label>
                        <input type="text" name="driver_name" placeholder="Nama supir pengantar" class="neo-input w-full px-3 py-2">
                    </div>
                </div>

                <!-- Items Delivery Allocation Table -->
                <div x-show="deliveryRows.length > 0" class="space-y-2 pt-2 border-t border-cream-300/80">
                    <label class="font-bold text-cream-900 uppercase font-mono tracking-wider text-[11px] block">
                        Alokasi Kuantitas Pengiriman (Bisa Parsial/Sebagian)
                    </label>

                    <div class="space-y-2">
                        <template x-for="(row, index) in deliveryRows" :key="index">
                            <div class="grid grid-cols-12 gap-2 items-center bg-cream-200/50 p-2.5 rounded-xl border border-cream-300/60">
                                <input type="hidden" :name="'items[' + index + '][so_item_id]'" :value="row.so_item_id">

                                <div class="col-span-12 sm:col-span-6">
                                    <span class="font-semibold text-cream-900 block" x-text="row.item_name"></span>
                                    <span class="text-[10px] text-cream-500 font-mono" x-text="'Sisa Pesanan Belum Kirim: ' + row.remaining_qty + ' ' + row.unit"></span>
                                </div>

                                <div class="col-span-12 sm:col-span-6 flex items-center justify-end space-x-2">
                                    <label class="text-[11px] text-cream-600 whitespace-nowrap">Kirim Sekarang:</label>
                                    <input type="number" step="0.01" min="0" :max="row.remaining_qty"
                                           :name="'items[' + index + '][qty_delivered]'" x-model="row.qty_delivered"
                                           class="neo-input w-28 px-2 py-1.5 font-mono text-center font-bold text-forest text-sm">
                                    <span class="font-mono text-xs text-cream-700" x-text="row.unit"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Catatan Tambahan Surat Jalan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan instruksi bongkar muatan..." class="neo-input w-full px-3 py-2"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-cream-300/80">
                    <button type="button" @click="createModalOpen = false" class="neo-btn px-4 py-2 font-medium text-cream-700">
                        Batal
                    </button>
                    <button type="submit" class="neo-btn-primary px-5 py-2 font-semibold" :disabled="deliveryRows.length === 0">
                        Terbitkan Surat Jalan & Potong Stok
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
