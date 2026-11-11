@extends('layouts.app')

@section('title', 'Master Pelanggan')

@section('content')
<div class="space-y-6" x-data="{ createModalOpen: false }">

    <!-- Top Action Bar -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-cream-900">Master Data Pelanggan (Customer)</h1>
            <p class="text-xs text-cream-600">
                Pencatatan profil mitra kontraktor & toko material, penetapan batas plafon kredit, dan koordinat lokasi proyek.
            </p>
        </div>

        @if(in_array(Auth::user()->role->code, ['ADMIN', 'MARKETING']))
            <button @click="createModalOpen = true" type="button" class="neo-btn-primary px-4 py-2.5 text-xs font-semibold flex items-center justify-center space-x-1.5 rounded-xl shadow-neo-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Daftarkan Pelanggan Baru</span>
            </button>
        @endif
    </div>

    <!-- Filter Bar -->
    <div class="neo-card p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <form method="GET" action="{{ route('customers.index') }}" class="flex-1 flex flex-col sm:flex-row items-center gap-2 w-full">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, kode pelanggan, alamat, atau no. telepon..."
                       class="neo-input w-full pl-9 pr-3 py-2 text-xs">
                <svg class="w-4 h-4 text-cream-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <button type="submit" class="neo-btn px-4 py-2 font-medium text-cream-800 hover:border-forest/50">
                Saring
            </button>
        </form>
    </div>

    <!-- Customers Table (Desktop) & Cards (Mobile) -->
    <div class="neo-card overflow-hidden">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-cream-300 bg-cream-100/60 font-mono text-cream-600 uppercase text-[11px]">
                        <th class="py-3 px-4">Kode</th>
                        <th class="py-3 px-4">Nama Pelanggan</th>
                        <th class="py-3 px-3">Telepon</th>
                        <th class="py-3 px-4">Alamat Proyek / Kantor</th>
                        <th class="py-3 px-3 text-right">Plafon Kredit</th>
                        <th class="py-3 px-3 text-right">Piutang Berjalan</th>
                        <th class="py-3 px-3 text-right">Sisa Plafon</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-300/60 font-mono">
                    @forelse ($customers as $c)
                        @php
                            $avail = max(0, (float)$c->credit_limit - (float)$c->current_credit);
                        @endphp
                        <tr class="hover:bg-cream-100/70 transition-colors">
                            <td class="py-3 px-4 font-semibold text-cream-900 whitespace-nowrap">
                                <a href="{{ route('customers.show', $c->id) }}" class="text-forest hover:underline">
                                    {{ $c->code }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-sans font-medium text-cream-900 min-w-[160px]">
                                {{ $c->name }}
                            </td>
                            <td class="py-3 px-3 text-cream-700 whitespace-nowrap">
                                {{ $c->phone ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-sans text-cream-600 max-w-xs truncate" title="{{ $c->address }}">
                                {{ $c->address ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-cream-900 whitespace-nowrap">
                                Rp {{ number_format($c->credit_limit, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right text-purple-900 font-semibold whitespace-nowrap">
                                Rp {{ number_format($c->current_credit, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold whitespace-nowrap {{ $avail > 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                Rp {{ number_format($avail, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <a href="{{ route('customers.show', $c->id) }}" class="neo-btn px-2.5 py-1 text-xs text-forest hover:border-forest/50 font-sans font-medium">
                                    Riwayat &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-cream-500 font-sans">
                                Belum ada pelanggan yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (Minimalist) -->
        <div class="block md:hidden divide-y divide-cream-300/60 font-sans">
            @forelse ($customers as $c)
                @php
                    $avail = max(0, (float)$c->credit_limit - (float)$c->current_credit);
                @endphp
                <div class="p-4 space-y-3 hover:bg-white/60 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-mono text-xs font-bold text-cream-900 block">{{ $c->code }}</span>
                            <h3 class="text-sm font-semibold text-cream-900 leading-snug">{{ $c->name }}</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ $avail > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $avail > 0 ? 'Kredit Aktif' : 'Limit Penuh' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 p-2.5 rounded-xl bg-slate-50/80 border border-slate-100 text-center font-mono text-xs">
                        <div>
                            <span class="text-[10px] text-cream-500 block font-sans">Plafon</span>
                            <span class="font-semibold text-cream-800 text-[11px]">Rp {{ number_format($c->credit_limit / 1000000, 0) }}jt</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-cream-500 block font-sans">Piutang</span>
                            <span class="font-semibold text-purple-900 text-[11px]">Rp {{ number_format($c->current_credit / 1000000, 1) }}jt</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-cream-500 block font-sans">Sisa Plafon</span>
                            <span class="font-bold {{ $avail > 0 ? 'text-emerald-700' : 'text-rose-700' }} text-[11px]">Rp {{ number_format($avail / 1000000, 1) }}jt</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1 text-xs">
                        <span class="text-[11px] text-cream-500 truncate max-w-[170px] font-mono">
                            {{ $c->phone ?? ($c->address ?? 'Tanpa kontak') }}
                        </span>
                        <a href="{{ route('customers.show', $c->id) }}" class="neo-btn px-3 py-1.5 text-xs font-medium text-forest rounded-lg">
                            Riwayat &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-cream-500 text-xs">
                    Belum ada pelanggan yang terdaftar.
                </div>
            @endforelse
        </div>

        <div class="p-4 border-t border-cream-300">
            {{ $customers->links() }}
        </div>
    </div>

    <!-- MODAL PENDAFTARAN PELANGGAN BARU -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-md flex items-center justify-center p-4">
        <div @click.away="createModalOpen = false" class="glass-card bg-white/95 max-w-lg w-full p-6 space-y-4 shadow-glass-lg rounded-2xl border border-white/80">
            <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
                <h3 class="text-sm font-bold text-cream-900">Daftarkan Pelanggan Baru</h3>
                <button @click="createModalOpen = false" class="neo-btn p-1.5 text-cream-500 hover:text-cream-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('customers.store') }}" class="space-y-4 text-xs">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Kode Pelanggan *</label>
                        <input type="text" name="code" placeholder="CUST-005" required class="neo-input w-full px-3 py-2 font-mono uppercase">
                    </div>
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Plafon Kredit (Rp) *</label>
                        <input type="number" step="0.01" name="credit_limit" placeholder="50000000" required class="neo-input w-full px-3 py-2 font-mono font-bold text-forest">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Nama Perusahaan / Toko *</label>
                    <input type="text" name="name" placeholder="PT / CV / Toko Bangunan" required class="neo-input w-full px-3 py-2">
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" placeholder="021-xxxx / 0812-xxxx" class="neo-input w-full px-3 py-2 font-mono">
                </div>

                <div>
                    <label class="block font-medium text-cream-700 mb-1">Alamat Proyek / Kantor</label>
                    <textarea name="address" rows="2" placeholder="Alamat lengkap tujuan bongkar muat..." class="neo-input w-full px-3 py-2"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Latitude (Koordinat Peta)</label>
                        <input type="number" step="any" name="latitude" placeholder="-6.2088" class="neo-input w-full px-3 py-2 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-cream-700 mb-1">Longitude</label>
                        <input type="number" step="any" name="longitude" placeholder="106.8456" class="neo-input w-full px-3 py-2 font-mono">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-cream-300/80">
                    <button type="button" @click="createModalOpen = false" class="neo-btn px-4 py-2 font-medium text-cream-700">
                        Batal
                    </button>
                    <button type="submit" class="neo-btn-primary px-5 py-2 font-semibold">
                        Simpan Pelanggan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
