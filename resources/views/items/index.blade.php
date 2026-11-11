@extends('layouts.app')

@section('title', 'Master Data Barang')

@section('content')
<div class="space-y-6" x-data="{ createModalOpen: false, editModalOpen: false, activeItem: {} }">

    <!-- Top Action Bar -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-cream-900">Master Data Barang Dagang</h1>
            <p class="text-xs text-cream-600">
                Pencatatan 7 parameter utama katalog: Kode, Nama, Satuan, Harga Beli, Harga Jual, Keterangan, & Stok Minimum.
            </p>
        </div>

        @if(in_array(Auth::user()->role->code, ['ADMIN', 'PURCHASING']))
            <button @click="createModalOpen = true" type="button" class="neo-btn-primary px-4 py-2.5 text-xs font-semibold flex items-center justify-center space-x-1.5 rounded-xl shadow-neo-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Input Master Barang Baru</span>
            </button>
        @endif
    </div>

    <!-- Filter & Search Bar -->
    <div class="neo-card p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <form method="GET" action="{{ route('items.index') }}" class="flex-1 flex flex-col sm:flex-row items-center gap-2 w-full">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama barang, kode SKU, atau keterangan..."
                       class="neo-input w-full pl-9 pr-3 py-2 text-xs">
                <svg class="w-4 h-4 text-cream-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <select name="filter" class="neo-input px-3 py-2 text-xs w-full sm:w-auto">
                    <option value="">Semua Kondisi Stok</option>
                    <option value="low_stock" {{ $filter === 'low_stock' ? 'selected' : '' }}>Stok Kritis (Batas Minimum)</option>
                </select>

                <button type="submit" class="neo-btn px-4 py-2 font-medium text-cream-800 hover:border-forest/50">
                    Saring
                </button>

                @if($search || $filter)
                    <a href="{{ route('items.index') }}" class="neo-btn px-3 py-2 text-rose-700 hover:bg-rose-50" title="Reset Filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Items Table -->
    <div class="neo-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-cream-300 bg-cream-100/60 font-mono text-cream-600 uppercase text-[11px]">
                        <th class="py-3 px-4">1. Kode Brg</th>
                        <th class="py-3 px-4">2. Nama Brg</th>
                        <th class="py-3 px-3">3. Satuan</th>
                        <th class="py-3 px-3 text-right">4. Harga Beli</th>
                        <th class="py-3 px-3 text-right">5. Harga Jual</th>
                        <th class="py-3 px-4">6. Keterangan</th>
                        <th class="py-3 px-3 text-right">7. Min Stok</th>
                        <th class="py-3 px-3 text-right">Stok Fisik</th>
                        <th class="py-3 px-3 text-right">Tersedia</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-300/60">
                    @forelse ($items as $item)
                        @php
                            $bal = $item->stockBalance;
                            $onHand = (float)($bal->on_hand ?? 0);
                            $reserved = (float)($bal->reserved ?? 0);
                            $available = (float)($bal->available ?? 0);
                            $isLow = $available <= (float)$item->min_stock;
                        @endphp
                        <tr class="hover:bg-cream-100/70 transition-colors {{ $isLow ? 'bg-rose-50/30' : '' }}">
                            <td class="py-3 px-4 font-mono font-semibold text-cream-900 whitespace-nowrap">
                                <a href="{{ route('items.show', $item->id) }}" class="text-forest hover:underline">
                                    {{ $item->code }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-medium text-cream-900 min-w-[160px]">
                                {{ $item->name }}
                            </td>
                            <td class="py-3 px-3 font-mono text-cream-700 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded bg-cream-300/60 text-[10px]">{{ $item->unit }}</span>
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-cream-700 whitespace-nowrap">
                                Rp {{ number_format($item->cost_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-semibold text-forest whitespace-nowrap">
                                Rp {{ number_format($item->selling_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-cream-600 max-w-xs truncate" title="{{ $item->notes }}">
                                {{ $item->notes ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-cream-600 whitespace-nowrap">
                                {{ number_format($item->min_stock, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-cream-800 whitespace-nowrap">
                                {{ number_format($onHand, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold whitespace-nowrap {{ $isLow ? 'text-rose-700' : 'text-emerald-700' }}">
                                {{ number_format($available, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <a href="{{ route('items.show', $item->id) }}" class="neo-btn p-1.5 text-cream-700 hover:text-forest" title="Kartu Riwayat Stok">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </a>

                                    @if(in_array(Auth::user()->role->code, ['ADMIN', 'PURCHASING']))
                                        <button @click="activeItem = {{ json_encode($item) }}; editModalOpen = true;" type="button"
                                                class="neo-btn p-1.5 text-cream-700 hover:text-amber-700" title="Ubah Data">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-cream-500">
                                Tidak ada data barang yang sesuai dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-cream-300">
            {{ $items->links() }}
        </div>
    </div>

    <!-- MODAL: INPUT MASTER BARANG BARU (7 PARAMETER + SALDO AWAL) -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="createModalOpen = false" class="neo-card bg-cream-100 max-w-xl w-full p-6 space-y-4 shadow-neo-lg rounded-2xl border border-cream-300">
            <div class="flex items-center justify-between border-b border-cream-300/80 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-cream-900">Input Data Master Barang Baru</h3>
                    <p class="text-[11px] text-cream-500">Isi lengkap 7 parameter wajib katalog barang dagang</p>
                </div>
                <button @click="createModalOpen = false" class="neo-btn p-1.5 text-cream-500 hover:text-cream-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('items.store') }}" class="space-y-4 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">1. Kode Barang (SKU) *</label>
                        <input type="text" name="code" placeholder="Contoh: BRG-021" required
                               class="neo-input w-full px-3 py-2 font-mono uppercase">
                    </div>
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">2. Nama Barang *</label>
                        <input type="text" name="name" placeholder="Nama lengkap produk dagang" required
                               class="neo-input w-full px-3 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">3. Satuan *</label>
                        <input type="text" name="unit" placeholder="Sak / Pcs / Dus / M3" required
                               class="neo-input w-full px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">4. Harga Beli (Rp) *</label>
                        <input type="number" step="0.01" name="cost_price" placeholder="0" required
                               class="neo-input w-full px-3 py-2 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">5. Harga Jual (Rp) *</label>
                        <input type="number" step="0.01" name="selling_price" placeholder="0" required
                               class="neo-input w-full px-3 py-2 font-mono text-forest font-semibold">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">6. Keterangan Produk</label>
                    <textarea name="notes" rows="2" placeholder="Spesifikasi teknis, merek, toleransi SNI, dll..."
                              class="neo-input w-full px-3 py-2"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 border-t border-cream-300/60">
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">7. Stok Minimum (Batas Peringatan) *</label>
                        <input type="number" step="0.01" name="min_stock" value="10" required
                               class="neo-input w-full px-3 py-2 font-mono text-rose-800">
                    </div>
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Saldo Awal Fisik di Gudang</label>
                        <input type="number" step="0.01" name="initial_stock" value="0"
                               class="neo-input w-full px-3 py-2 font-mono text-cream-900">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-cream-300/80">
                    <button type="button" @click="createModalOpen = false" class="neo-btn px-4 py-2 font-medium text-cream-700">
                        Batal
                    </button>
                    <button type="submit" class="neo-btn-primary px-5 py-2 font-semibold">
                        Simpan Master Barang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT DATA BARANG -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="editModalOpen = false" class="neo-card bg-cream-100 max-w-xl w-full p-6 space-y-4 shadow-neo-lg rounded-2xl border border-cream-300">
            <div class="flex items-center justify-between border-b border-cream-300/80 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-cream-900">Perbarui Master Barang</h3>
                    <p class="text-[11px] text-cream-500 font-mono" x-text="activeItem.code"></p>
                </div>
                <button @click="editModalOpen = false" class="neo-btn p-1.5 text-cream-500 hover:text-cream-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'/items/' + activeItem.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Kode Barang (SKU) *</label>
                        <input type="text" name="code" x-model="activeItem.code" required
                               class="neo-input w-full px-3 py-2 font-mono uppercase">
                    </div>
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Nama Barang *</label>
                        <input type="text" name="name" x-model="activeItem.name" required
                               class="neo-input w-full px-3 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Satuan *</label>
                        <input type="text" name="unit" x-model="activeItem.unit" required
                               class="neo-input w-full px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Harga Beli (Rp) *</label>
                        <input type="number" step="0.01" name="cost_price" x-model="activeItem.cost_price" required
                               class="neo-input w-full px-3 py-2 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Harga Jual (Rp) *</label>
                        <input type="number" step="0.01" name="selling_price" x-model="activeItem.selling_price" required
                               class="neo-input w-full px-3 py-2 font-mono text-forest font-semibold">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Keterangan Produk</label>
                    <textarea name="notes" rows="2" x-model="activeItem.notes"
                              class="neo-input w-full px-3 py-2"></textarea>
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Stok Minimum *</label>
                    <input type="number" step="0.01" name="min_stock" x-model="activeItem.min_stock" required
                           class="neo-input w-full px-3 py-2 font-mono text-rose-800">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-cream-300/80">
                    <button type="button" @click="editModalOpen = false" class="neo-btn px-4 py-2 font-medium text-cream-700">
                        Batal
                    </button>
                    <button type="submit" class="neo-btn-primary px-5 py-2 font-semibold">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
