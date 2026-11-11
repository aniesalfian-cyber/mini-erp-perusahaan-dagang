@extends('layouts.app')

@section('title', 'Profil Pelanggan: ' . $customer->name)

@section('content')
<div class="space-y-6">

    <!-- Top Action & Breadcrumb -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-cream-500 mb-1">
                <a href="{{ route('customers.index') }}" class="hover:underline">Pelanggan</a>
                <span>/</span>
                <span class="font-mono text-cream-800">{{ $customer->code }}</span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-cream-900">{{ $customer->name }}</h1>
            <p class="text-xs text-cream-600 mt-0.5">{{ $customer->address ?? 'Alamat belum tercatat.' }} | Telp: {{ $customer->phone ?? '-' }}</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('customers.index') }}" class="neo-btn px-3.5 py-2 text-xs font-medium text-cream-700 rounded-xl">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Credit Information Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="neo-card p-4 space-y-1">
            <span class="text-[11px] font-mono uppercase text-cream-500">Plafon Kredit Disetujui</span>
            <p class="text-2xl font-mono font-bold text-cream-900">
                Rp {{ number_format($customer->credit_limit, 0, ',', '.') }}
            </p>
            <p class="text-[11px] text-cream-500 font-sans">Batas maksimal hutang berjalan</p>
        </div>

        <div class="neo-card p-4 space-y-1">
            <span class="text-[11px] font-mono uppercase text-cream-500">Total Piutang Berjalan</span>
            <p class="text-2xl font-mono font-bold text-purple-900">
                Rp {{ number_format($customer->current_credit, 0, ',', '.') }}
            </p>
            <p class="text-[11px] text-cream-500 font-sans">Faktur belum terlunasi</p>
        </div>

        <div class="neo-card p-4 space-y-1 border-l-4 border-l-forest">
            <span class="text-[11px] font-mono uppercase text-cream-500">Sisa Plafon Siap Belanja</span>
            <p class="text-2xl font-mono font-bold text-forest">
                Rp {{ number_format($customer->available_credit, 0, ',', '.') }}
            </p>
            <p class="text-[11px] text-cream-500 font-sans">Kapasitas pemesanan SO berikutnya</p>
        </div>
    </div>

    <!-- Recent Sales Orders -->
    <div class="neo-card p-6 space-y-4">
        <h2 class="text-xs font-bold text-cream-900 uppercase font-mono tracking-wider">
            10 Pesanan Penjualan Terakhir (Sales Order)
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-cream-300 text-cream-500 font-mono text-[11px]">
                        <th class="py-2.5 px-3">No. SO</th>
                        <th class="py-2.5 px-3">Tanggal</th>
                        <th class="py-2.5 px-3 text-right">Nilai Total</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-300/60 font-mono">
                    @forelse ($customer->salesOrders as $so)
                        <tr class="hover:bg-cream-100/60">
                            <td class="py-2.5 px-3 font-semibold text-cream-900">{{ $so->order_no }}</td>
                            <td class="py-2.5 px-3 text-cream-600">{{ $so->order_date->format('d/m/Y') }}</td>
                            <td class="py-2.5 px-3 text-right font-bold text-cream-900">Rp {{ number_format($so->total_amount, 0, ',', '.') }}</td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-cream-300 text-cream-800">{{ $so->status }}</span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <a href="{{ route('sales-orders.show', $so->id) }}" class="text-forest hover:underline font-sans">Lihat &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-cream-500 font-sans">Belum ada riwayat pesanan dari pelanggan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
