@extends('layouts.app')

@section('title', 'Beranda Analitik')

@section('content')
<div class="space-y-6">

    <!-- Top Hero Banner with Luxury Glass Identity -->
    <div class="glass-card p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-white/90 bg-white/80 shadow-glass">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 rounded-full text-[11px] font-mono font-semibold tracking-wider uppercase shadow-sm
                    {{ $role === 'ADMIN' ? 'bg-forest text-white' : ($role === 'MARKETING' ? 'bg-emerald-700 text-white' : ($role === 'PURCHASING' ? 'bg-amber-700 text-white' : ($role === 'LOGISTIK' ? 'bg-blue-700 text-white' : 'bg-purple-700 text-white'))) }}">
                    Divisi {{ $user->role->name ?? $role }}
                </span>
                <span class="text-xs text-cream-500 font-mono">{{ Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-cream-900 mt-1.5 tracking-tight">
                Selamat Datang, {{ $user->name }}
            </h1>
            <p class="text-xs text-cream-600 mt-0.5">
                Panel kendali terpadu perdagangan & distribusi bahan bangunan Prima Jaya.
            </p>
        </div>

        <!-- Quick Action Shortcuts -->
        <div class="flex flex-wrap items-center gap-2">
            @if(in_array($role, ['ADMIN', 'MARKETING']))
                <a href="{{ route('sales-orders.index') }}" class="glass-btn-primary px-4 py-2.5 text-xs font-semibold flex items-center space-x-1.5 rounded-xl shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Input Pesanan (SO)</span>
                </a>
            @endif

            @if(in_array($role, ['ADMIN', 'PURCHASING']))
                <a href="{{ route('purchase-orders.index') }}" class="glass-btn px-4 py-2.5 text-xs font-semibold flex items-center space-x-1.5 rounded-xl hover:border-forest/50">
                    <svg class="w-4 h-4 text-forest" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>Terbitkan PO Supplier</span>
                </a>
            @endif

            <a href="{{ route('backorders.index') }}" class="glass-btn-terracotta px-4 py-2.5 text-xs font-semibold flex items-center space-x-1.5 rounded-xl shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Pantau Backorder ({{ $totalBackorderLines }})</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards (Luxury White Glassmorphism) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Penjualan -->
        <div class="glass-card p-5 space-y-2.5 border-t-2 border-t-emerald-600/80">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-cream-600">Total Nilai Penjualan</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-100 flex items-center justify-center shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-xl sm:text-2xl font-mono font-bold text-cream-900 tracking-tight">
                Rp {{ number_format($totalSales, 0, ',', '.') }}
            </p>
            <div class="text-[11px] text-cream-500 flex items-center justify-between pt-1 border-t border-slate-100">
                <span>Pesanan Aktif: {{ $openOrdersCount }} SO</span>
                <span class="text-emerald-700 font-semibold">Order-to-Cash</span>
            </div>
        </div>

        <!-- 2. Piutang Usaha Pelanggan -->
        <div class="glass-card p-5 space-y-2.5 border-t-2 border-t-purple-600/80">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-cream-600">Sisa Piutang Berjalan</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-800 border border-purple-100 flex items-center justify-center shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                </div>
            </div>
            <p class="text-xl sm:text-2xl font-mono font-bold text-purple-950 tracking-tight">
                Rp {{ number_format($totalReceivable, 0, ',', '.') }}
            </p>
            <div class="text-[11px] text-cream-500 flex items-center justify-between pt-1 border-t border-slate-100">
                <span>Faktur Belum Lunas: {{ $unpaidInvoicesCount }}</span>
                <span class="text-purple-700 font-semibold">Accounting</span>
            </div>
        </div>

        <!-- 3. Outstanding Backorder (PO Belum Dikirimkan) -->
        <div class="glass-card p-5 space-y-2.5 border-t-2 border-t-terracotta">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-terracotta">PO Belum Kirim (Backorder)</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-800 border border-amber-100 flex items-center justify-center shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-xl sm:text-2xl font-mono font-bold text-terracotta tracking-tight">
                {{ number_format($totalBackorderQty, 0, ',', '.') }} <span class="text-xs font-normal">Unit</span>
            </p>
            <div class="text-[11px] text-cream-500 flex items-center justify-between pt-1 border-t border-slate-100">
                <span>{{ $totalBackorderLines }} Baris Tertunda</span>
                <a href="{{ route('backorders.index') }}" class="text-terracotta font-semibold hover:underline">Rincian &rarr;</a>
            </div>
        </div>

        <!-- 4. Peringatan Stok Minimum -->
        <div class="glass-card p-5 space-y-2.5 border-t-2 border-t-rose-500">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-rose-700">Stok Kritis / Menipis</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-700 border border-rose-100 flex items-center justify-center shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <p class="text-xl sm:text-2xl font-mono font-bold text-rose-800 tracking-tight">
                {{ $lowStockItems->count() }} <span class="text-xs font-normal">Item</span>
            </p>
            <div class="text-[11px] text-cream-500 flex items-center justify-between pt-1 border-t border-slate-100">
                <span>Di bawah ambang batas</span>
                <a href="{{ route('items.index', ['filter' => 'low_stock']) }}" class="text-rose-700 font-semibold hover:underline">Lihat &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Analytics Chart & Geographic Distribution Map (White Glass Surfaces) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Chart.js Monthly Sales -->
        <div class="glass-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-cream-900 tracking-tight">Tren Penjualan Bulanan</h2>
                    <p class="text-[11px] text-cream-500">Omzet transaksi Sales Order 6 bulan terakhir</p>
                </div>
                <span class="text-[11px] font-mono px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">Chart.js</span>
            </div>
            <div class="h-64 relative">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Leaflet Interactive Map -->
        <div class="glass-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-cream-900 tracking-tight">Peta Sebaran Pelanggan & Pemasok</h2>
                    <p class="text-[11px] text-cream-500">Pemantauan titik lokasi gudang supplier dan tujuan pengiriman</p>
                </div>
                <div class="flex items-center space-x-2 text-[10px] font-mono">
                    <span class="flex items-center space-x-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span><span>Pelanggan</span></span>
                    <span class="flex items-center space-x-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span><span>Pemasok</span></span>
                </div>
            </div>
            <div id="leafletMap" class="h-64 rounded-xl border border-slate-200/80 z-10 shadow-inner"></div>
        </div>

    </div>

    <!-- Tables: Backorders & Low Stock Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Backorder Widget -->
        <div class="glass-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-cream-900 tracking-tight">Barang PO Belum Dikirim (Backorder Prioritas)</h2>
                    <p class="text-[11px] text-cream-500">Pesanan penjualan aktif yang kuantitasnya belum terpenuhi penuh</p>
                </div>
                <a href="{{ route('backorders.index') }}" class="text-xs text-terracotta font-semibold hover:underline">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 text-cream-500 font-mono text-[11px]">
                            <th class="pb-2.5">No. SO</th>
                            <th class="pb-2.5">Pelanggan</th>
                            <th class="pb-2.5 text-right">Sisa Qty</th>
                            <th class="pb-2.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono">
                        @forelse ($recentOrders->whereIn('status', ['CONFIRMED', 'PROCESSING', 'PARTIAL_SHIPPED']) as $order)
                            <tr class="hover:bg-white/60 transition-colors">
                                <td class="py-3 font-semibold text-cream-900">
                                    <a href="{{ route('sales-orders.show', $order->id) }}" class="hover:underline text-forest">
                                        {{ $order->order_no }}
                                    </a>
                                </td>
                                <td class="py-3 font-sans truncate max-w-[140px] text-cream-800">
                                    {{ $order->customer->name }}
                                </td>
                                <td class="py-3 text-right font-bold text-terracotta">
                                    {{ number_format($order->pending_backorder_qty, 0, ',', '.') }}
                                </td>
                                <td class="py-3 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-semibold
                                        {{ $order->status === 'PARTIAL_SHIPPED' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-5 text-center text-cream-500 font-sans">
                                    Tidak ada pesanan tertunda (Seluruh pesanan terpenuhi).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Items Alert Widget -->
        <div class="glass-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-cream-900 tracking-tight">Peringatan Stok di Bawah Batas Minimum</h2>
                    <p class="text-[11px] text-cream-500">Katalog barang yang membutuhkan penerbitan PO pengadaan segera</p>
                </div>
                <a href="{{ route('items.index', ['filter' => 'low_stock']) }}" class="text-xs text-rose-700 font-semibold hover:underline">
                    Kelola Stok &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 text-cream-500 font-mono text-[11px]">
                            <th class="pb-2.5">Kode / Nama Barang</th>
                            <th class="pb-2.5 text-right">Tersedia</th>
                            <th class="pb-2.5 text-right">Min Stok</th>
                            <th class="pb-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono">
                        @forelse ($lowStockItems->take(5) as $item)
                            <tr class="hover:bg-white/60 transition-colors">
                                <td class="py-3">
                                    <span class="font-semibold text-cream-900 block truncate max-w-[150px] font-sans">{{ $item->name }}</span>
                                    <span class="text-[10px] text-cream-500">{{ $item->code }} ({{ $item->unit }})</span>
                                </td>
                                <td class="py-3 text-right font-bold {{ $item->stockBalance->available <= 0 ? 'text-rose-600' : 'text-amber-700' }}">
                                    {{ number_format($item->stockBalance->available, 0, ',', '.') }}
                                </td>
                                <td class="py-3 text-right text-cream-600">
                                    {{ number_format($item->min_stock, 0, ',', '.') }}
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('purchase-orders.index') }}" class="glass-btn px-2.5 py-1 text-[11px] font-sans text-forest hover:border-forest/50">
                                        Reorder
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-5 text-center text-emerald-700 font-sans">
                                    Seluruh stok barang berada di atas ambang aman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Inisialisasi Chart.js Penjualan
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthLabels) !!},
                datasets: [{
                    label: 'Omzet Penjualan (Rp)',
                    data: {!! json_encode($monthlySales) !!},
                    borderColor: '#0F2E22',
                    backgroundColor: 'rgba(15, 46, 34, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#0F2E22',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { family: 'Plus Jakarta Sans', size: 12 },
                        bodyFont: { family: 'JetBrains Mono', size: 12 },
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000) + ' Jt';
                            },
                            font: { family: 'JetBrains Mono', size: 10 },
                            color: '#64748B'
                        },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' }
                    },
                    x: {
                        ticks: { font: { family: 'JetBrains Mono', size: 10 }, color: '#64748B' },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Inisialisasi Leaflet.js Peta
    const mapElement = document.getElementById('leafletMap');
    if (mapElement && typeof L !== 'undefined') {
        const locations = {!! json_encode($mapLocations) !!};
        const defaultCenter = locations.length > 0 ? [locations[0].lat, locations[0].lng] : [-6.2088, 106.8456];
        const map = L.map('leafletMap').setView(defaultCenter, 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        locations.forEach(loc => {
            const isCustomer = loc.type === 'customer';
            const color = isCustomer ? '#059669' : '#2563EB';
            const marker = L.circleMarker([loc.lat, loc.lng], {
                radius: 8,
                fillColor: color,
                color: '#FFFFFF',
                weight: 2.5,
                opacity: 1,
                fillOpacity: 0.9
            }).addTo(map);

            const popupContent = `
                <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; line-height: 1.4; padding: 2px;">
                    <strong style="color: ${color}; font-size: 13px;">${isCustomer ? 'Pelanggan' : 'Pemasok'}: ${loc.name}</strong><br>
                    <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; color: #64748B;">${loc.code}</span><br>
                    ${loc.phone ? '<span style="color: #334155;">Telp: ' + loc.phone + '</span><br>' : ''}
                    <small style="color: #64748B;">${loc.address || ''}</small>
                </div>
            `;
            marker.bindPopup(popupContent);
        });
    }
});
</script>
@endpush
