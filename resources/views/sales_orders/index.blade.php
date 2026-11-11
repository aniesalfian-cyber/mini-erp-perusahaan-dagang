@extends('layouts.app')

@section('title', 'Pesanan Penjualan (Sales Order)')

@section('content')
<div class="space-y-6" x-data="{
    createModalOpen: false,
    selectedCustomerId: '',
    customers: {{ json_encode($customers) }},
    items: {{ json_encode($items) }},
    orderRows: [
        { item_id: '', qty: 1, unit_price: 0, subtotal: 0 }
    ],
    updateRow(index) {
        const row = this.orderRows[index];
        const item = this.items.find(i => i.id == row.item_id);
        if (item) {
            row.unit_price = parseFloat(item.selling_price) || 0;
            row.subtotal = (parseFloat(row.qty) || 0) * row.unit_price;
        } else {
            row.unit_price = 0;
            row.subtotal = 0;
        }
    },
    addRow() {
        this.orderRows.push({ item_id: '', qty: 1, unit_price: 0, subtotal: 0 });
    },
    removeRow(index) {
        if (this.orderRows.length > 1) {
            this.orderRows.splice(index, 1);
        }
    },
    get grandTotal() {
        return this.orderRows.reduce((acc, row) => acc + (parseFloat(row.subtotal) || 0), 0);
    },
    get selectedCustomer() {
        return this.customers.find(c => c.id == this.selectedCustomerId);
    },
    get availableCredit() {
        if (!this.selectedCustomer) return 0;
        return Math.max(0, parseFloat(this.selectedCustomer.credit_limit) - parseFloat(this.selectedCustomer.current_credit));
    },
    get isOverLimit() {
        if (!this.selectedCustomer) return false;
        return this.grandTotal > this.availableCredit;
    }
}">

    <!-- Top Action Bar -->
    <div class="glass-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-white/90 bg-white/80 shadow-glass">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-cream-900 tracking-tight">Pesanan Penjualan (Sales Order / PO Masuk)</h1>
            <p class="text-xs text-cream-600">
                Divisi Marketing: Penerimaan PO dari pelanggan, validasi plafon kredit, dan reservasi otomatis stok gudang.
            </p>
        </div>

        @if(in_array(Auth::user()->role->code, ['ADMIN', 'MARKETING']))
            <button @click="createModalOpen = true" type="button" class="glass-btn-primary px-4 py-2.5 text-xs font-semibold flex items-center justify-center space-x-1.5 rounded-xl shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Input Sales Order Baru</span>
            </button>
        @endif
    </div>

    <!-- Filter Bar -->
    <div class="glass-card p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs border border-white/80 bg-white/75 shadow-glass-sm">
        <form method="GET" action="{{ route('sales-orders.index') }}" class="flex-1 flex flex-col sm:flex-row items-center gap-2 w-full">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor SO atau nama pelanggan..."
                       class="glass-input w-full pl-9 pr-3 py-2 text-xs">
                <svg class="w-4 h-4 text-cream-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <select name="status" class="glass-input px-3 py-2 text-xs w-full sm:w-auto">
                    <option value="ALL">Semua Status</option>
                    <option value="CONFIRMED" {{ $status === 'CONFIRMED' ? 'selected' : '' }}>CONFIRMED (Terkonfirmasi)</option>
                    <option value="PARTIAL_SHIPPED" {{ $status === 'PARTIAL_SHIPPED' ? 'selected' : '' }}>PARTIAL_SHIPPED (Terkirim Sebagian)</option>
                    <option value="COMPLETED" {{ $status === 'COMPLETED' ? 'selected' : '' }}>COMPLETED (Selesai Penuh)</option>
                    <option value="CANCELLED" {{ $status === 'CANCELLED' ? 'selected' : '' }}>CANCELLED (Batal)</option>
                </select>

                <button type="submit" class="glass-btn px-4 py-2 font-medium text-cream-800 hover:border-forest/50">
                    Saring
                </button>
            </div>
        </form>
    </div>

    <!-- Orders Table (Luxury White Glass) -->
    <div class="glass-card overflow-hidden border border-white/80 bg-white/80 shadow-glass">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/80 font-mono text-cream-600 uppercase text-[11px]">
                        <th class="py-3 px-4">No. Order (SO)</th>
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-3">Sales / Marketing</th>
                        <th class="py-3 px-3 text-right">Nilai Total</th>
                        <th class="py-3 px-3 text-right">Sisa Backorder</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-white/90 transition-colors">
                            <td class="py-3 px-4 font-mono font-semibold text-cream-900 whitespace-nowrap">
                                <a href="{{ route('sales-orders.show', $order->id) }}" class="text-forest hover:underline">
                                    {{ $order->order_no }}
                                </a>
                            </td>
                            <td class="py-3 px-3 font-mono text-cream-600 whitespace-nowrap">
                                {{ $order->order_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4 font-medium text-cream-900 min-w-[150px]">
                                {{ $order->customer->name }}
                            </td>
                            <td class="py-3 px-3 text-cream-700 whitespace-nowrap">
                                {{ $order->salesUser->name ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-cream-900 whitespace-nowrap">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-semibold whitespace-nowrap {{ $order->pending_backorder_qty > 0 ? 'text-terracotta' : 'text-cream-400' }}">
                                {{ number_format($order->pending_backorder_qty, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-semibold
                                    {{ $order->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' : ($order->status === 'PARTIAL_SHIPPED' ? 'bg-amber-100 text-amber-800' : ($order->status === 'CONFIRMED' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700')) }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <a href="{{ route('sales-orders.show', $order->id) }}" class="glass-btn px-3 py-1 text-xs text-forest hover:border-forest/50 font-medium">
                                    Rincian &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-cream-500">
                                Belum ada dokumen Sales Order yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
    </div>

    <!-- MODAL INPUT SALES ORDER BARU (LUXURY GLASS) -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-md flex items-center justify-center p-4">
        <div @click.away="createModalOpen = false" class="glass-card bg-white/95 max-w-3xl w-full p-6 space-y-4 shadow-glass-lg rounded-3xl border border-white/90 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-cream-900">Input Pesanan Penjualan Baru (Sales Order)</h3>
                    <p class="text-[11px] text-cream-500">Periksa limit kredit pelanggan dan reservasi stok otomatis</p>
                </div>
                <button @click="createModalOpen = false" class="glass-btn p-1.5 text-cream-500 hover:text-cream-900 rounded-xl">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('sales-orders.store') }}" class="space-y-4 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Pilih Pelanggan *</label>
                        <select name="customer_id" x-model="selectedCustomerId" required class="glass-input w-full px-3 py-2">
                            <option value="">-- Pilih Pelanggan --</option>
                            <template x-for="cust in customers" :key="cust.id">
                                <option :value="cust.id" x-text="cust.name + ' (' + cust.code + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Tanggal Pesanan *</label>
                        <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required class="glass-input w-full px-3 py-2 font-mono">
                    </div>
                </div>

                <!-- Customer Credit Alert Badge -->
                <div x-show="selectedCustomer" class="glass-inset p-3 rounded-2xl border border-slate-200/80 text-xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-cream-600">Plafon Kredit Pelanggan:</span>
                        <strong class="font-mono text-cream-900" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selectedCustomer ? selectedCustomer.credit_limit : 0)"></strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-cream-600">Sisa Plafon Tersedia:</span>
                        <strong class="font-mono" :class="isOverLimit ? 'text-rose-700 font-bold' : 'text-emerald-700 font-bold'"
                                x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(availableCredit)"></strong>
                    </div>
                    <p x-show="isOverLimit" class="text-rose-700 font-medium pt-1">
                        Peringatan: Nilai pesanan melampaui sisa plafon kredit pelanggan!
                    </p>
                </div>

                <!-- Items Dynamic Table -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-cream-900 uppercase font-mono tracking-wider text-[11px]">Daftar Barang Dipesan</label>
                        <button type="button" @click="addRow()" class="glass-btn px-3 py-1 text-[11px] font-semibold text-forest hover:border-forest/50">
                            + Tambah Baris Barang
                        </button>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(row, index) in orderRows" :key="index">
                            <div class="grid grid-cols-12 gap-2 items-center bg-slate-50/70 p-2.5 rounded-2xl border border-slate-200/60">
                                <div class="col-span-12 sm:col-span-5">
                                    <select :name="'items[' + index + '][item_id]'" x-model="row.item_id" @change="updateRow(index)" required class="glass-input w-full px-2.5 py-1.5 text-xs">
                                        <option value="">-- Pilih Barang Dagang --</option>
                                        <template x-for="item in items" :key="item.id">
                                            <option :value="item.id" x-text="item.name + ' (' + item.unit + ') - Stok: ' + (item.stock_balance ? item.stock_balance.available : 0)"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="col-span-4 sm:col-span-2">
                                    <input type="number" step="0.01" :name="'items[' + index + '][qty]'" x-model="row.qty" @input="updateRow(index)" min="0.01" placeholder="Qty" required class="glass-input w-full px-2 py-1.5 font-mono text-center">
                                </div>

                                <div class="col-span-4 sm:col-span-2">
                                    <input type="number" step="0.01" :name="'items[' + index + '][unit_price]'" x-model="row.unit_price" @input="updateRow(index)" min="0" placeholder="Harga" required class="glass-input w-full px-2 py-1.5 font-mono text-right text-forest font-semibold">
                                </div>

                                <div class="col-span-3 sm:col-span-2 text-right font-mono font-bold text-cream-900 truncate" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(row.subtotal || 0)">
                                </div>

                                <div class="col-span-1 text-center">
                                    <button type="button" @click="removeRow(index)" class="text-rose-600 hover:text-rose-800 p-1" title="Hapus baris">
                                        &times;
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Catatan Pesanan Pelanggan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan alamat pengiriman khusus atau permintaan jadwal..." class="glass-input w-full px-3 py-2"></textarea>
                </div>

                <!-- Grand Total Display -->
                <div class="flex items-center justify-between p-3.5 glass-inset rounded-2xl font-mono">
                    <span class="text-xs font-semibold text-cream-800">Total Nilai Pesanan (SO):</span>
                    <strong class="text-base font-bold text-forest" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal)"></strong>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="createModalOpen = false" class="glass-btn px-4 py-2 font-medium text-cream-700">
                        Batal
                    </button>
                    <button type="submit" class="glass-btn-primary px-5 py-2 font-semibold" :disabled="isOverLimit">
                        Konfirmasi Pesanan & Reservasi Stok
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
