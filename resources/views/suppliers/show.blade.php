@extends('layouts.app')

@section('title', 'Profil Pemasok: ' . $supplier->name)

@section('content')
<div class="space-y-6">

    <!-- Top Action & Breadcrumb -->
    <div class="neo-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-cream-500 mb-1">
                <a href="{{ route('suppliers.index') }}" class="hover:underline">Pemasok</a>
                <span>/</span>
                <span class="font-mono text-cream-800">{{ $supplier->code }}</span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-cream-900">{{ $supplier->name }}</h1>
            <p class="text-xs text-cream-600 mt-0.5">{{ $supplier->address ?? 'Alamat belum tercatat.' }} | Telp: {{ $supplier->phone ?? '-' }}</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('suppliers.index') }}" class="neo-btn px-3.5 py-2 text-xs font-medium text-cream-700 rounded-xl">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="neo-card p-4 space-y-2">
            <span class="text-[11px] font-mono uppercase text-cream-500">Kontak Person Resmi (PIC)</span>
            <p class="text-base font-bold text-cream-900">{{ $supplier->contact_person ?? 'Belum ditentukan' }}</p>
            <p class="text-xs text-cream-600 font-mono">Telepon: {{ $supplier->phone ?? '-' }}</p>
        </div>

        <div class="neo-card p-4 space-y-2">
            <span class="text-[11px] font-mono uppercase text-cream-500">Koordinat Pabrik / Gudang Pemasok</span>
            <p class="text-xs text-cream-700 font-mono">
                Lat: {{ $supplier->latitude ?? '-' }} | Lng: {{ $supplier->longitude ?? '-' }}
            </p>
            <p class="text-[11px] text-cream-500 font-sans">Terintegrasi dalam peta navigasi pengemudi & armada</p>
        </div>
    </div>

    <!-- Recent Purchase Orders -->
    <div class="neo-card p-6 space-y-4">
        <h2 class="text-xs font-bold text-cream-900 uppercase font-mono tracking-wider">
            10 Purchase Order Terakhir ke Pemasok Ini
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-cream-300 text-cream-500 font-mono text-[11px]">
                        <th class="py-2.5 px-3">No. PO</th>
                        <th class="py-2.5 px-3">Tanggal</th>
                        <th class="py-2.5 px-3 text-right">Nilai Total</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-300/60 font-mono">
                    @forelse ($supplier->purchaseOrders as $po)
                        <tr class="hover:bg-cream-100/60">
                            <td class="py-2.5 px-3 font-semibold text-cream-900">{{ $po->po_no }}</td>
                            <td class="py-2.5 px-3 text-cream-600">{{ $po->po_date->format('d/m/Y') }}</td>
                            <td class="py-2.5 px-3 text-right font-bold text-cream-900">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-cream-300 text-cream-800">{{ $po->status }}</span>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <a href="{{ route('purchase-orders.show', $po->id) }}" class="text-forest hover:underline font-sans">Lihat &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-cream-500 font-sans">Belum ada pesanan pembelian ke pemasok ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
