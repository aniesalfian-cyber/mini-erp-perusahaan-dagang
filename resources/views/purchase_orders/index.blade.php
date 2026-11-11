@extends('layouts.app')

@section('title', 'Pengadaan: Purchase Order (PO)')

@section('content')
<div class="space-y-6" x-data="{
    createModalOpen: false,
    suppliers: {{ json_encode($suppliers) }},
    items: {{ json_encode($items) }},
    poRows: [
        { item_id: '', qty: 10, unit_cost: 0, subtotal: 0 }
    ],
    updateRow(index) {
        const row = this.poRows[index];
        const item = this.items.find(i => i.id == row.item_id);
        if (item) {
            row.unit_cost = parseFloat(item.cost_price) || 0;
            row.subtotal = (parseFloat(row.qty) || 0) * row.unit_cost;
        } else {
            row.unit_cost = 0;
            row.subtotal = 0;
        }
    },
    addRow() {
        this.poRows.push({ item_id: '', qty: 10, unit_cost: 0, subtotal: 0 });
    },
    removeRow(index) {
        if (this.poRows.length > 1) {
            this.poRows.splice(index, 1);
        }
    },
    get grandTotal() {
        return this.poRows.reduce((acc, row) => acc + (parseFloat(row.subtotal) || 0), 0);
    }
}">

    <!-- Top Action Bar -->
    <div class="glass-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-white/90 bg-white/80 shadow-glass">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-cream-900 tracking-tight">Divisi Pengadaan: Purchase Order (PO Keluar)</h1>
            <p class="text-xs text-cream-600">
                Penerbitan pesanan pembelian resmi kepada pabrikan & distributor bahan bangunan untuk menjaga ketersediaan stok.
            </p>
        </div>

        @if(in_array(Auth::user()->role->code, ['ADMIN', 'PURCHASING']))
            <button @click="createModalOpen = true" type="button" class="glass-btn-primary px-4 py-2.5 text-xs font-semibold flex items-center justify-center space-x-1.5 rounded-xl shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Terbitkan PO Supplier Baru</span>
            </button>
        @endif
    </div>

    <!-- Filter Bar -->
    <div class="glass-card p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs border border-white/80 bg-white/75 shadow-glass-sm">
        <form method="GET" action="{{ route('purchase-orders.index') }}" class="flex-1 flex flex-col sm:flex-row items-center gap-2 w-full">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor PO atau nama pemasok..."
                       class="glass-input w-full pl-9 pr-3 py-2 text-xs">
                <svg class="w-4 h-4 text-cream-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <select name="status" class="glass-input px-3 py-2 text-xs w-full sm:w-auto">
                    <option value="ALL">Semua Status</option>
                    <option value="ISSUED" {{ $status === 'ISSUED' ? 'selected' : '' }}>ISSUED (Diterbitkan ke Supplier)</option>
                    <option value="PARTIAL_RECEIVED" {{ $status === 'PARTIAL_RECEIVED' ? 'selected' : '' }}>PARTIAL_RECEIVED (Diterima Sebagian)</option>
                    <option value="COMPLETED" {{ $status === 'COMPLETED' ? 'selected' : '' }}>COMPLETED (Selesai Penuh)</option>
                </select>

                <button type="submit" class="glass-btn px-4 py-2 font-medium text-cream-800 hover:border-forest/50">
                    Saring
                </button>
            </div>
        </form>
    </div>

    <!-- PO Table (Desktop) & Cards (Mobile) -->
    <div class="glass-card overflow-hidden border border-white/80 bg-white/80 shadow-glass">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/80 font-mono text-cream-600 uppercase text-[11px]">
                        <th class="py-3 px-4">No. PO</th>
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-4">Pemasok (Supplier)</th>
                        <th class="py-3 px-3">Purchasing</th>
                        <th class="py-3 px-3 text-right">Nilai Total</th>
                        <th class="py-3 px-3">Target Tiba</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($purchaseOrders as $po)
                        <tr class="hover:bg-white/90 transition-colors">
                            <td class="py-3 px-4 font-mono font-semibold text-cream-900 whitespace-nowrap">
                                <a href="{{ route('purchase-orders.show', $po->id) }}" class="text-forest hover:underline">
                                    {{ $po->po_no }}
                                </a>
                            </td>
                            <td class="py-3 px-3 font-mono text-cream-600 whitespace-nowrap">
                                {{ $po->po_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4 font-medium text-cream-900 min-w-[150px]">
                                {{ $po->supplier->name }}
                            </td>
                            <td class="py-3 px-3 text-cream-700 whitespace-nowrap">
                                {{ $po->purchasingUser->name ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-cream-900 whitespace-nowrap">
                                Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 font-mono text-cream-600 whitespace-nowrap">
                                {{ $po->expected_date ? $po->expected_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-semibold
                                    {{ $po->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' : ($po->status === 'PARTIAL_RECEIVED' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ $po->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <a href="{{ route('purchase-orders.print', $po->id) }}" target="_blank" class="glass-btn p-1.5 text-slate-700 hover:text-forest" title="Cetak Purchase Order">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>
                                    <a href="{{ route('purchase-orders.show', $po->id) }}" class="glass-btn px-2.5 py-1 text-xs text-forest hover:border-forest/50 font-medium">
                                        Rincian &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-cream-500">
                                Belum ada Purchase Order yang diterbitkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (Minimalist) -->
        <div class="block md:hidden divide-y divide-slate-100 font-sans">
            @forelse ($purchaseOrders as $po)
                <div class="p-4 space-y-3 hover:bg-white/60 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-mono text-xs font-bold text-cream-900">{{ $po->po_no }}</span>
                                <span class="text-[10px] text-cream-500 font-mono">{{ $po->po_date->format('d/m/Y') }}</span>
                            </div>
                            <h3 class="text-sm font-semibold text-cream-900 leading-snug mt-0.5">{{ $po->supplier->name }}</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold whitespace-nowrap
                            {{ $po->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' : ($po->status === 'PARTIAL_RECEIVED' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ $po->status }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/80 border border-slate-100 text-xs font-mono">
                        <div>
                            <span class="text-[10px] text-cream-500 font-sans block">Total Anggaran PO</span>
                            <span class="font-bold text-cream-900 text-sm">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-cream-500 font-sans block">Target Tiba</span>
                            <span class="font-semibold text-cream-700">
                                {{ $po->expected_date ? $po->expected_date->format('d/m/Y') : 'Reguler' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1 text-xs">
                        <span class="text-[11px] text-cream-500 truncate max-w-[150px]">
                            Purchasing: {{ $po->purchasingUser->name ?? 'Staf' }}
                        </span>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('purchase-orders.print', $po->id) }}" target="_blank" class="glass-btn px-2.5 py-1.5 text-xs text-slate-700 flex items-center space-x-1 rounded-lg">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Cetak</span>
                            </a>
                            <a href="{{ route('purchase-orders.show', $po->id) }}" class="glass-btn px-3 py-1.5 text-xs font-medium text-forest rounded-lg">
                                Rincian &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-cream-500 text-xs">
                    Belum ada Purchase Order yang diterbitkan.
                </div>
            @endforelse
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $purchaseOrders->links() }}
        </div>
    </div>

    <!-- MODAL INPUT PO SUPPLIER BARU (LUXURY GLASS) -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-md flex items-center justify-center p-4">
        <div @click.away="createModalOpen = false" class="glass-card bg-white/95 max-w-3xl w-full p-6 space-y-4 shadow-glass-lg rounded-3xl border border-white/90 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-cream-900">Terbitkan Purchase Order Baru</h3>
                    <p class="text-[11px] text-cream-500">Pesanan barang ke distributor atau pabrikan resmi</p>
                </div>
                <button @click="createModalOpen = false" class="glass-btn p-1.5 text-cream-500 hover:text-cream-900 rounded-xl">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-4 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-1">
                        <label class="block font-medium text-cream-700 mb-1">Pilih Pemasok *</label>
                        <select name="supplier_id" required class="glass-input w-full px-3 py-2">
                            <option value="">-- Pilih Supplier --</option>
                            <template x-for="supp in suppliers" :key="supp.id">
                                <option :value="supp.id" x-text="supp.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Tanggal PO *</label>
                        <input type="date" name="po_date" value="{{ date('Y-m-d') }}" required class="glass-input w-full px-3 py-2 font-mono">
                    </div>

                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Estimasi Tanggal Tiba</label>
                        <input type="date" name="expected_date" value="{{ date('Y-m-d', strtotime('+3 days')) }}" class="glass-input w-full px-3 py-2 font-mono">
                    </div>
                </div>

                <!-- Items Dynamic Table -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-cream-900 uppercase font-mono tracking-wider text-[11px]">Daftar Barang yang Dipesan</label>
                        <button type="button" @click="addRow()" class="glass-btn px-3 py-1 text-[11px] font-semibold text-forest hover:border-forest/50">
                            + Tambah Baris
                        </button>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(row, index) in poRows" :key="index">
                            <div class="grid grid-cols-12 gap-2 items-center bg-slate-50/70 p-2.5 rounded-2xl border border-slate-200/60">
                                <div class="col-span-12 sm:col-span-5">
                                    <select :name="'items[' + index + '][item_id]'" x-model="row.item_id" @change="updateRow(index)" required class="glass-input w-full px-2.5 py-1.5 text-xs">
                                        <option value="">-- Pilih Barang --</option>
                                        <template x-for="item in items" :key="item.id">
                                            <option :value="item.id" x-text="item.name + ' (' + item.unit + ')'"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="col-span-4 sm:col-span-2">
                                    <input type="number" step="0.01" :name="'items[' + index + '][qty]'" x-model="row.qty" @input="updateRow(index)" min="0.01" placeholder="Qty" required class="glass-input w-full px-2 py-1.5 font-mono text-center">
                                </div>

                                <div class="col-span-4 sm:col-span-2">
                                    <input type="number" step="0.01" :name="'items[' + index + '][unit_cost]'" x-model="row.unit_cost" @input="updateRow(index)" min="0" placeholder="Biaya Beli" required class="glass-input w-full px-2 py-1.5 font-mono text-right text-cream-900">
                                </div>

                                <div class="col-span-3 sm:col-span-2 text-right font-mono font-bold text-cream-900 truncate" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(row.subtotal || 0)">
                                </div>

                                <div class="col-span-1 text-center">
                                    <button type="button" @click="removeRow(index)" class="text-rose-600 hover:text-rose-800 p-1">
                                        &times;
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Catatan Tambahan untuk Pemasok</label>
                    <textarea name="notes" rows="2" placeholder="Instruksi pengiriman kontainer atau waktu buka gerbang..." class="glass-input w-full px-3 py-2"></textarea>
                </div>

                <!-- Grand Total Display -->
                <div class="flex items-center justify-between p-3.5 glass-inset rounded-2xl font-mono">
                    <span class="text-xs font-semibold text-cream-800">Total Biaya Pengadaan (PO):</span>
                    <strong class="text-base font-bold text-forest" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal)"></strong>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="createModalOpen = false" class="glass-btn px-4 py-2 font-medium text-cream-700">
                        Batal
                    </button>
                    <button type="submit" class="glass-btn-primary px-5 py-2 font-semibold">
                        Keluarkan Purchase Order
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
