@extends('layouts.app')

@section('title', 'Logistik: Penerimaan Barang (GRN)')

@section('content')
<div class="space-y-6" x-data="{
    createModalOpen: false,
    selectedPoId: '',
    openPurchaseOrders: {{ json_encode($openPurchaseOrders) }},
    receiptRows: [],
    supplierDoNo: '',
    receiptDate: '{{ date('Y-m-d') }}',
    onPoChange() {
        const po = this.openPurchaseOrders.find(p => p.id == this.selectedPoId);
        if (po && po.items) {
            this.receiptRows = po.items.map(item => {
                const remaining = Math.max(0, parseFloat(item.qty_ordered) - parseFloat(item.qty_received));
                return {
                    po_item_id: item.id,
                    item_name: item.item ? item.item.name : 'Barang',
                    unit: item.item ? item.item.unit : '',
                    remaining_qty: remaining,
                    qty_received: remaining,
                    qty_accepted: remaining,
                    qty_rejected: 0,
                    reject_reason: '',
                };
            }).filter(r => r.remaining_qty > 0);
        } else {
            this.receiptRows = [];
        }
    },
    updateAccepted(index) {
        const row = this.receiptRows[index];
        const rec = parseFloat(row.qty_received) || 0;
        const rej = parseFloat(row.qty_rejected) || 0;
        row.qty_accepted = Math.max(0, rec - rej);
    }
}">

    <!-- Top Action Bar -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-cream-900">Divisi Gudang: Nota Penerimaan Barang (GRN)</h1>
            <p class="text-xs text-cream-600">
                Pencatatan barang masuk dari pemasok, inspeksi kualitas fisik, pemisahan barang cacat/rusak, & penambahan stok otomatis.
            </p>
        </div>

        @if(in_array(Auth::user()->role->code, ['ADMIN', 'LOGISTIK']))
            <button @click="createModalOpen = true" type="button" class="neo-btn-primary px-4 py-2.5 text-xs font-semibold flex items-center justify-center space-x-1.5 rounded-xl shadow-neo-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Input Penerimaan Barang Baru</span>
            </button>
        @endif
    </div>

    <!-- Filter Bar -->
    <div class="neo-card p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <form method="GET" action="{{ route('goods-receipts.index') }}" class="flex-1 flex flex-col sm:flex-row items-center gap-2 w-full">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor GRN, surat jalan supplier, atau pemasok..."
                       class="neo-input w-full pl-9 pr-3 py-2 text-xs">
                <svg class="w-4 h-4 text-cream-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <select name="status" class="neo-input px-3 py-2 text-xs w-full sm:w-auto">
                    <option value="ALL">Semua Status</option>
                    <option value="ACCEPTED" {{ $status === 'ACCEPTED' ? 'selected' : '' }}>ACCEPTED (Lolos & Masuk Stok)</option>
                    <option value="INSPECTING" {{ $status === 'INSPECTING' ? 'selected' : '' }}>INSPECTING (Sedang Diinspeksi)</option>
                </select>

                <button type="submit" class="neo-btn px-4 py-2 font-medium text-cream-800 hover:border-forest/50">
                    Saring
                </button>
            </div>
        </form>
    </div>

    <!-- GRN Table -->
    <div class="neo-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-cream-300 bg-cream-100/60 font-mono text-cream-600 uppercase text-[11px]">
                        <th class="py-3 px-4">No. Penerimaan (GRN)</th>
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">No. PO Referensi</th>
                        <th class="py-3 px-4">Pemasok</th>
                        <th class="py-3 px-3">No. SJ Supplier</th>
                        <th class="py-3 px-3">Petugas Gudang</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-300/60">
                    @forelse ($goodsReceipts as $grn)
                        <tr class="hover:bg-cream-100/70 transition-colors">
                            <td class="py-3 px-4 font-mono font-semibold text-cream-900 whitespace-nowrap">
                                <a href="{{ route('goods-receipts.show', $grn->id) }}" class="text-forest hover:underline">
                                    {{ $grn->grn_no }}
                                </a>
                            </td>
                            <td class="py-3 px-3 font-mono text-cream-600 whitespace-nowrap">
                                {{ $grn->receipt_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-3 font-mono text-cream-800 whitespace-nowrap">
                                <a href="{{ route('purchase-orders.show', $grn->purchase_order_id) }}" class="hover:underline text-cream-700">
                                    {{ $grn->purchaseOrder->po_no ?? '-' }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-medium text-cream-900 min-w-[150px]">
                                {{ $grn->supplier->name }}
                            </td>
                            <td class="py-3 px-3 font-mono text-cream-700 whitespace-nowrap">
                                {{ $grn->supplier_do_no ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-cream-600 whitespace-nowrap">
                                {{ $grn->logisticsUser->name ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-semibold bg-emerald-100 text-emerald-800">
                                    {{ $grn->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <a href="{{ route('goods-receipts.show', $grn->id) }}" class="neo-btn px-2.5 py-1 text-xs text-forest hover:border-forest/50 font-medium">
                                    Rincian &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-cream-500">
                                Belum ada Nota Penerimaan Barang yang dicatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-cream-300">
            {{ $goodsReceipts->links() }}
        </div>
    </div>

    <!-- MODAL INPUT NOTA PENERIMAAN GUDANG (GRN) -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="createModalOpen = false" class="neo-card bg-cream-100 max-w-3xl w-full p-6 space-y-4 shadow-neo-lg rounded-2xl border border-cream-300 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-cream-300/80 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-cream-900">Input Penerimaan Barang Masuk (Goods Receipt)</h3>
                    <p class="text-[11px] text-cream-500">Inspeksi fisik barang dari supplier & update stok gudang</p>
                </div>
                <button @click="createModalOpen = false" class="neo-btn p-1.5 text-cream-500 hover:text-cream-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('goods-receipts.store') }}" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Pilih Purchase Order (PO Terbit yang Belum Tiba Penuh) *</label>
                    <select name="purchase_order_id" x-model="selectedPoId" @change="onPoChange()" required class="neo-input w-full px-3 py-2 font-mono">
                        <option value="">-- Pilih Purchase Order --</option>
                        <template x-for="po in openPurchaseOrders" :key="po.id">
                            <option :value="po.id" x-text="po.po_no + ' - ' + (po.supplier ? po.supplier.name : '') + ' (' + po.status + ')'"></option>
                        </template>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Tanggal Diterima di Gudang *</label>
                        <input type="date" name="receipt_date" x-model="receiptDate" required class="neo-input w-full px-3 py-2 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Nomor Surat Jalan dari Pemasok</label>
                        <input type="text" name="supplier_do_no" placeholder="Contoh: SJ-SUPP-8821" class="neo-input w-full px-3 py-2 font-mono uppercase">
                    </div>
                </div>

                <!-- Inspection Allocation Table -->
                <div x-show="receiptRows.length > 0" class="space-y-2 pt-2 border-t border-cream-300/80">
                    <label class="font-bold text-cream-900 uppercase font-mono tracking-wider text-[11px] block">
                        Inspeksi Kualitas Fisik Barang (Diterima vs Lolos vs Ditolak)
                    </label>

                    <div class="space-y-2.5">
                        <template x-for="(row, index) in receiptRows" :key="index">
                            <div class="bg-cream-200/50 p-3 rounded-xl border border-cream-300/60 space-y-2">
                                <input type="hidden" :name="'items[' + index + '][po_item_id]'" :value="row.po_item_id">

                                <div class="flex items-center justify-between">
                                    <strong class="text-cream-900" x-text="row.item_name"></strong>
                                    <span class="text-[10px] text-cream-500 font-mono" x-text="'Sisa PO: ' + row.remaining_qty + ' ' + row.unit"></span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <div>
                                        <label class="text-[10px] text-cream-600 block">Total Diterima Fisik:</label>
                                        <input type="number" step="0.01" min="0" :max="row.remaining_qty"
                                               :name="'items[' + index + '][qty_received]'" x-model="row.qty_received" @input="updateAccepted(index)"
                                               class="neo-input w-full px-2 py-1 font-mono text-center font-bold text-cream-900">
                                    </div>

                                    <div>
                                        <label class="text-[10px] text-emerald-800 font-semibold block">Lolos (Masuk Stok Gudang):</label>
                                        <input type="number" step="0.01" min="0"
                                               :name="'items[' + index + '][qty_accepted]'" x-model="row.qty_accepted"
                                               class="neo-input w-full px-2 py-1 font-mono text-center font-bold text-emerald-700 bg-emerald-50/40">
                                    </div>

                                    <div>
                                        <label class="text-[10px] text-rose-700 font-semibold block">Ditolak (Cacat/Rusak):</label>
                                        <input type="number" step="0.01" min="0"
                                               :name="'items[' + index + '][qty_rejected]'" x-model="row.qty_rejected" @input="updateAccepted(index)"
                                               class="neo-input w-full px-2 py-1 font-mono text-center font-bold text-rose-700 bg-rose-50/40">
                                    </div>
                                </div>

                                <div x-show="row.qty_rejected > 0">
                                    <input type="text" :name="'items[' + index + '][reject_reason]'" x-model="row.reject_reason"
                                           placeholder="Alasan penolakan: sak robek, basah, karat, dll..."
                                           class="neo-input w-full px-2.5 py-1 text-xs text-rose-800 placeholder-rose-300 border-rose-300">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Catatan Tambahan Penerimaan Gudang</label>
                    <textarea name="notes" rows="2" placeholder="Catatan kondisi kontainer saat bongkar..." class="neo-input w-full px-3 py-2"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-cream-300/80">
                    <button type="button" @click="createModalOpen = false" class="neo-btn px-4 py-2 font-medium text-cream-700">
                        Batal
                    </button>
                    <button type="submit" class="neo-btn-primary px-5 py-2 font-semibold" :disabled="receiptRows.length === 0">
                        Simpan Penerimaan & Tambah Stok
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
