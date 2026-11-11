@extends('layouts.app')

@section('title', 'Kartu Stok: ' . $item->name)

@section('content')
<div class="space-y-6" x-data="{ adjustModalOpen: false }">

    <!-- Header & Breadcrumb -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-cream-500 mb-1">
                <a href="{{ route('items.index') }}" class="hover:underline">Master Barang</a>
                <span>/</span>
                <span class="font-mono text-cream-800">{{ $item->code }}</span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-cream-900">{{ $item->name }}</h1>
            <p class="text-xs text-cream-600 mt-0.5">{{ $item->notes ?? 'Tidak ada keterangan spesifikasi tambahan.' }}</p>
        </div>

        <div class="flex items-center space-x-2">
            @if(Auth::user()->isAdmin())
                <button @click="adjustModalOpen = true" type="button" class="neo-btn px-3 py-2 text-xs font-semibold text-rose-800 hover:bg-rose-50 flex items-center space-x-1.5 rounded-xl">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    <span>Penyesuaian Stok Opname</span>
                </button>
            @endif

            <a href="{{ route('items.index') }}" class="neo-btn px-3.5 py-2 text-xs font-medium text-cream-700 rounded-xl">
                &larr; Kembali ke Katalog
            </a>
        </div>
    </div>

    <!-- Parameter & Balance Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="neo-card p-4 space-y-1">
            <span class="text-[11px] font-mono text-cream-500 uppercase">Satuan & Harga</span>
            <div class="text-xs space-y-1">
                <p><span class="text-cream-500">Satuan:</span> <strong class="font-mono text-cream-900">{{ $item->unit }}</strong></p>
                <p><span class="text-cream-500">Beli:</span> <strong class="font-mono text-cream-800">Rp {{ number_format($item->cost_price, 0, ',', '.') }}</strong></p>
                <p><span class="text-cream-500">Jual:</span> <strong class="font-mono text-forest">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</strong></p>
            </div>
        </div>

        <div class="neo-card p-4 space-y-1">
            <span class="text-[11px] font-mono text-cream-500 uppercase">Stok Fisik Gudang</span>
            <p class="text-2xl font-mono font-bold text-cream-900">
                {{ number_format($item->stockBalance->on_hand ?? 0, 0, ',', '.') }} <span class="text-xs font-normal font-sans">{{ $item->unit }}</span>
            </p>
            <p class="text-[11px] text-cream-500 font-sans">Kuantitas aktual di rak gudang</p>
        </div>

        <div class="neo-card p-4 space-y-1">
            <span class="text-[11px] font-mono text-cream-500 uppercase">Stok Direservasi (SO)</span>
            <p class="text-2xl font-mono font-bold text-amber-700">
                {{ number_format($item->stockBalance->reserved ?? 0, 0, ',', '.') }} <span class="text-xs font-normal font-sans">{{ $item->unit }}</span>
            </p>
            <p class="text-[11px] text-cream-500 font-sans">Telah dipesan pelanggan aktif</p>
        </div>

        <div class="neo-card p-4 space-y-1 border-l-4 border-l-forest">
            <span class="text-[11px] font-mono text-cream-500 uppercase">Stok Bersih Siap Jual</span>
            <p class="text-2xl font-mono font-bold text-forest">
                {{ number_format($item->stockBalance->available ?? 0, 0, ',', '.') }} <span class="text-xs font-normal font-sans">{{ $item->unit }}</span>
            </p>
            <p class="text-[11px] text-cream-500 font-sans">Batas Minimum: {{ number_format($item->min_stock, 0, ',', '.') }} {{ $item->unit }}</p>
        </div>
    </div>

    <!-- Stock Ledger / Mutasi History -->
    <div class="neo-card overflow-hidden">
        <div class="p-4 border-b border-cream-300 bg-cream-100/50 flex items-center justify-between">
            <h2 class="text-xs font-bold text-cream-900 uppercase font-mono tracking-wider">
                Kartu Riwayat Pergerakan Stok (Append-Only Ledger)
            </h2>
            <span class="text-[11px] text-cream-500 font-mono">Total Transaksi: {{ $ledger->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-cream-300 text-cream-500 font-mono text-[11px]">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-3">Tipe Mutasi</th>
                        <th class="py-3 px-3">No. Referensi</th>
                        <th class="py-3 px-3 text-right">Masuk (+)</th>
                        <th class="py-3 px-3 text-right">Keluar (-)</th>
                        <th class="py-3 px-3 text-right">Saldo Akhir</th>
                        <th class="py-3 px-4">Keterangan</th>
                        <th class="py-3 px-3">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-300/60 font-mono">
                    @forelse ($ledger as $log)
                        <tr class="hover:bg-cream-100/60">
                            <td class="py-2.5 px-4 text-cream-600 whitespace-nowrap">
                                {{ $log->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px]
                                    {{ str_contains($log->transaction_type, 'IN') || $log->transaction_type === 'PO_RECEIPT' || $log->transaction_type === 'INITIAL' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $log->transaction_type }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 font-semibold text-cream-900 whitespace-nowrap">
                                {{ $log->reference_no ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-emerald-700 font-semibold whitespace-nowrap">
                                {{ (float)$log->qty_in > 0 ? '+' . number_format($log->qty_in, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-rose-700 font-semibold whitespace-nowrap">
                                {{ (float)$log->qty_out > 0 ? '-' . number_format($log->qty_out, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold text-cream-900 whitespace-nowrap">
                                {{ number_format($log->balance_after, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-4 text-cream-700 font-sans max-w-xs truncate" title="{{ $log->notes }}">
                                {{ $log->notes ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-cream-600 font-sans whitespace-nowrap">
                                {{ $log->user->name ?? 'Sistem' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-cream-500 font-sans">
                                Belum ada riwayat mutasi stok pada barang ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-cream-300">
            {{ $ledger->links() }}
        </div>
    </div>

    <!-- MODAL PENYESUAIAN STOK OPNAME (ADMIN ONLY) -->
    @if(Auth::user()->isAdmin())
        <div x-show="adjustModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="adjustModalOpen = false" class="neo-card bg-cream-100 max-w-md w-full p-6 space-y-4 shadow-neo-lg rounded-2xl border border-cream-300">
                <div class="flex items-center justify-between border-b border-cream-300/80 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-cream-900">Penyesuaian Stok Opname</h3>
                        <p class="text-[11px] text-cream-500 font-mono">{{ $item->code }} - {{ $item->name }}</p>
                    </div>
                    <button @click="adjustModalOpen = false" class="neo-btn p-1.5 text-cream-500 hover:text-cream-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('items.adjust', $item->id) }}" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Stok Fisik Saat Ini di Sistem</label>
                        <input type="text" value="{{ number_format($item->stockBalance->on_hand ?? 0, 0, ',', '.') }} {{ $item->unit }}" disabled
                               class="neo-input w-full px-3 py-2 bg-cream-200/50 font-mono text-cream-600">
                    </div>

                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Stok Fisik Hasil Hitung Nyata Gudang *</label>
                        <input type="number" step="0.01" name="new_on_hand" required
                               class="neo-input w-full px-3 py-2 font-mono font-bold text-forest text-sm">
                    </div>

                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Alasan Penyesuaian *</label>
                        <textarea name="reason" rows="2" placeholder="Contoh: Selisih fisik hasil stock opname bulanan..." required
                                  class="neo-input w-full px-3 py-2"></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-cream-300/80">
                        <button type="button" @click="adjustModalOpen = false" class="neo-btn px-4 py-2 font-medium text-cream-700">
                            Batal
                        </button>
                        <button type="submit" class="neo-btn-primary px-5 py-2 font-semibold bg-rose-700 hover:bg-rose-800">
                            Terapkan Penyesuaian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
