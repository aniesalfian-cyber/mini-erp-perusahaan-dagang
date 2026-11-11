<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | ERP Prima Jaya</title>

    <!-- PWA Settings -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1E3A2B">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-cream-200 text-cream-800 min-h-screen flex flex-col antialiased selection:bg-forest selection:text-white pb-24 md:pb-10">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-cream-200/90 backdrop-blur-md border-b border-cream-300/60 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand & Logo -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 group">
                        <div class="w-9 h-9 rounded-xl bg-forest flex items-center justify-center text-white font-mono font-semibold shadow-neo-sm group-hover:scale-105 transition-transform">
                            PJ
                        </div>
                        <div>
                            <span class="text-base font-semibold tracking-tight text-cream-900 block leading-tight">Prima Jaya</span>
                            <span class="text-[10px] font-mono tracking-wider text-forest uppercase block">Trading ERP</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden md:flex items-center space-x-1 font-medium text-xs">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-forest text-white shadow-neo-sm' : 'text-cream-700 hover:text-cream-900 hover:bg-cream-100' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('items.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('items.*') ? 'bg-forest text-white shadow-neo-sm' : 'text-cream-700 hover:text-cream-900 hover:bg-cream-100' }}">
                        Master Barang
                    </a>
                    <a href="{{ route('sales-orders.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('sales-orders.*') ? 'bg-forest text-white shadow-neo-sm' : 'text-cream-700 hover:text-cream-900 hover:bg-cream-100' }}">
                        Penjualan (SO)
                    </a>
                    <a href="{{ route('backorders.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('backorders.*') ? 'bg-terracotta text-white shadow-neo-sm' : 'text-terracotta hover:bg-terracotta-50' }}">
                        Laporan Backorder
                    </a>
                    <a href="{{ route('delivery-orders.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('delivery-orders.*') ? 'bg-forest text-white shadow-neo-sm' : 'text-cream-700 hover:text-cream-900 hover:bg-cream-100' }}">
                        Logistik (DO)
                    </a>
                    <a href="{{ route('purchase-orders.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('purchase-orders.*') ? 'bg-forest text-white shadow-neo-sm' : 'text-cream-700 hover:text-cream-900 hover:bg-cream-100' }}">
                        Pengadaan (PO)
                    </a>
                    <a href="{{ route('goods-receipts.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('goods-receipts.*') ? 'bg-forest text-white shadow-neo-sm' : 'text-cream-700 hover:text-cream-900 hover:bg-cream-100' }}">
                        Penerimaan (GRN)
                    </a>
                    <a href="{{ route('invoices.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('invoices.*') ? 'bg-forest text-white shadow-neo-sm' : 'text-cream-700 hover:text-cream-900 hover:bg-cream-100' }}">
                        Faktur & Keuangan
                    </a>
                </nav>

                <!-- User Profile & Quick Role Switcher -->
                <div class="flex items-center space-x-3" x-data="{ roleMenuOpen: false }">
                    <!-- Quick Switch Role Dropdown -->
                    <div class="relative">
                        <button @click="roleMenuOpen = !roleMenuOpen" type="button" class="neo-btn px-2.5 py-1.5 text-xs text-cream-800 flex items-center space-x-1.5 rounded-lg hover:border-forest/40">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="font-medium hidden sm:inline">{{ Auth::user()->role->name ?? 'User' }}</span>
                            <span class="font-medium sm:hidden">{{ Auth::user()->role->code ?? 'Role' }}</span>
                            <svg class="w-3.5 h-3.5 text-cream-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="roleMenuOpen" @click.away="roleMenuOpen = false" x-cloak
                             class="absolute right-0 mt-2 w-64 neo-card bg-cream-100 p-2 z-50 text-xs shadow-neo-lg rounded-xl border border-cream-300">
                            <div class="px-3 py-2 border-b border-cream-300/60 mb-1">
                                <p class="text-[10px] uppercase font-mono tracking-wider text-cream-500">Ganti Akun Demo (1-Klik)</p>
                                <p class="font-semibold text-cream-900 truncate">{{ Auth::user()->name }}</p>
                            </div>
                            <div class="space-y-1">
                                <a href="{{ route('quick-login', 'ADMIN') }}" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-cream-200 transition-colors {{ Auth::user()->hasRole('ADMIN') ? 'bg-cream-300/80 font-semibold' : '' }}">
                                    <span>Administrator / Owner</span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-cream-300 text-cream-800">ADMIN</span>
                                </a>
                                <a href="{{ route('quick-login', 'MARKETING') }}" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-cream-200 transition-colors {{ Auth::user()->hasRole('MARKETING') ? 'bg-cream-300/80 font-semibold' : '' }}">
                                    <span>Penjualan (Marketing)</span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-cream-300 text-cream-800">MARKETING</span>
                                </a>
                                <a href="{{ route('quick-login', 'PURCHASING') }}" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-cream-200 transition-colors {{ Auth::user()->hasRole('PURCHASING') ? 'bg-cream-300/80 font-semibold' : '' }}">
                                    <span>Pengadaan (Purchasing)</span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-cream-300 text-cream-800">PURCHASING</span>
                                </a>
                                <a href="{{ route('quick-login', 'LOGISTIK') }}" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-cream-200 transition-colors {{ Auth::user()->hasRole('LOGISTIK') ? 'bg-cream-300/80 font-semibold' : '' }}">
                                    <span>Gudang & Logistik</span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-cream-300 text-cream-800">LOGISTIK</span>
                                </a>
                                <a href="{{ route('quick-login', 'ACCOUNTING') }}" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-cream-200 transition-colors {{ Auth::user()->hasRole('ACCOUNTING') ? 'bg-cream-300/80 font-semibold' : '' }}">
                                    <span>Keuangan & Akuntansi</span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-cream-300 text-cream-800">ACCOUNTING</span>
                                </a>
                            </div>
                            <div class="mt-2 pt-2 border-t border-cream-300/60">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-3 py-1.5 text-rose-700 hover:bg-rose-50 rounded-lg transition-colors font-medium">
                                        Keluar dari Sistem
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Logout Icon -->
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit" title="Keluar" class="neo-btn p-2 text-cream-600 hover:text-rose-700 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if (session('success'))
            <div class="neo-card-sm bg-emerald-50/80 border border-emerald-200 p-4 rounded-xl flex items-start space-x-3 text-sm text-emerald-900 mb-4 animate-fade-in" role="alert">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="flex-1">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="neo-card-sm bg-rose-50/80 border border-rose-200 p-4 rounded-xl flex items-start space-x-3 text-sm text-rose-900 mb-4 animate-fade-in" role="alert">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="flex-1">{{ session('error') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="neo-card-sm bg-rose-50/80 border border-rose-200 p-4 rounded-xl text-sm text-rose-900 mb-4" role="alert">
                <p class="font-semibold mb-1">Terdapat kesalahan input:</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Body -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-4">
        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation (5 Core Divisions / Touch-First) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-cream-200/95 backdrop-blur-md border-t border-cream-300 shadow-neo-lg px-2 py-2">
        <div class="grid grid-cols-5 gap-1 text-center">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 rounded-lg {{ request()->routeIs('dashboard') ? 'text-forest font-semibold' : 'text-cream-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="text-[10px] mt-0.5">Beranda</span>
            </a>
            <a href="{{ route('sales-orders.index') }}" class="flex flex-col items-center py-1 rounded-lg {{ request()->routeIs('sales-orders.*') ? 'text-forest font-semibold' : 'text-cream-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span class="text-[10px] mt-0.5">Penjualan</span>
            </a>
            <a href="{{ route('backorders.index') }}" class="flex flex-col items-center py-1 rounded-lg {{ request()->routeIs('backorders.*') ? 'text-terracotta font-semibold' : 'text-cream-600' }}">
                <div class="relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-[10px] mt-0.5">Backorder</span>
            </a>
            <a href="{{ route('delivery-orders.index') }}" class="flex flex-col items-center py-1 rounded-lg {{ request()->routeIs('delivery-orders.*') ? 'text-forest font-semibold' : 'text-cream-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                <span class="text-[10px] mt-0.5">Logistik</span>
            </a>
            <a href="{{ route('invoices.index') }}" class="flex flex-col items-center py-1 rounded-lg {{ request()->routeIs('invoices.*') ? 'text-forest font-semibold' : 'text-cream-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                <span class="text-[10px] mt-0.5">Faktur</span>
            </a>
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
