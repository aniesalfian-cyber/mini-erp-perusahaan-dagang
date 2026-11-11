@extends('layouts.app')

@section('title', 'Audit Trail Aktivitas')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-cream-900">Audit Trail Aktivitas Sistem</h1>
            <p class="text-xs text-cream-600">
                Pencatatan log transaksi hukum finansial & mutasi data demi transparansi kepatuhan dan integritas perusahaan.
            </p>
        </div>
    </div>

    <!-- Logs Table (Desktop) & Cards (Mobile) -->
    <div class="neo-card overflow-hidden">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-cream-300 bg-cream-100/60 font-mono text-cream-600 uppercase text-[11px]">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-3">Pengguna</th>
                        <th class="py-3 px-3">Tindakan (Action)</th>
                        <th class="py-3 px-3">Entitas Tabel</th>
                        <th class="py-3 px-3">ID Rekaman</th>
                        <th class="py-3 px-3">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-300/60 font-mono">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-cream-100/70 transition-colors">
                            <td class="py-2.5 px-4 text-cream-600 whitespace-nowrap">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="py-2.5 px-3 font-sans font-medium text-cream-900 whitespace-nowrap">
                                {{ $log->user->name ?? 'Sistem' }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-cream-300 text-cream-800 font-semibold">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-cream-700 whitespace-nowrap">
                                {{ $log->table_name }}
                            </td>
                            <td class="py-2.5 px-3 text-cream-600 whitespace-nowrap">
                                #{{ $log->record_id }}
                            </td>
                            <td class="py-2.5 px-3 text-cream-500 whitespace-nowrap">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-cream-500 font-sans">
                                Belum ada catatan aktivitas log audit.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (Minimalist) -->
        <div class="block md:hidden divide-y divide-cream-300/60 font-sans">
            @forelse ($logs as $log)
                <div class="p-3.5 space-y-2 hover:bg-white/60 transition-colors text-xs">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-semibold text-cream-900">{{ $log->user->name ?? 'Sistem' }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 font-mono font-semibold">
                            {{ $log->action }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-cream-600 font-mono text-[11px]">
                        <span>Tabel: <strong class="text-cream-800">{{ $log->table_name }}</strong> #{{ $log->record_id }}</span>
                        <span class="text-cream-400">{{ $log->ip_address ?? '127.0.0.1' }}</span>
                    </div>

                    <div class="text-[10px] text-cream-500 font-mono pt-1 border-t border-slate-100">
                        {{ $log->created_at->format('d/m/Y H:i:s') }} WIB
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-cream-500 font-sans text-xs">
                    Belum ada catatan aktivitas log audit.
                </div>
            @endforelse
        </div>

        <div class="p-4 border-t border-cream-300">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
