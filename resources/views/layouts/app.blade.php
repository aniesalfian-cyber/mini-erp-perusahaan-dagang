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
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PrimaJaya ERP">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="text-cream-900 min-h-screen flex flex-col antialiased selection:bg-forest selection:text-white pb-24 md:pb-10">

    <!-- Top Navigation Bar (Luxury Frosted White Glass) -->
    <header class="sticky top-0 z-40 bg-white/70 backdrop-blur-xl border-b border-white/80 shadow-glass-sm transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand & Logo -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-forest-800 to-forest-600 flex items-center justify-center text-white font-mono font-semibold shadow-md group-hover:scale-105 transition-transform border border-white/20">
                            PJ
                        </div>
                        <div>
                            <span class="text-base font-semibold tracking-tight text-cream-900 block leading-tight">Prima Jaya</span>
                            <span class="text-[10px] font-mono tracking-wider text-forest-700 uppercase block font-semibold">Luxury Glass ERP</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden md:flex items-center space-x-1 font-medium text-xs">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-forest text-white shadow-glass-sm' : 'text-cream-600 hover:text-cream-900 hover:bg-white/80' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('items.index') }}" class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('items.*') ? 'bg-forest text-white shadow-glass-sm' : 'text-cream-600 hover:text-cream-900 hover:bg-white/80' }}">
                        Master Barang
                    </a>
                    <a href="{{ route('sales-orders.index') }}" class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('sales-orders.*') ? 'bg-forest text-white shadow-glass-sm' : 'text-cream-600 hover:text-cream-900 hover:bg-white/80' }}">
                        Penjualan (SO)
                    </a>
                    <a href="{{ route('backorders.index') }}" class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('backorders.*') ? 'bg-terracotta text-white shadow-glass-sm' : 'text-terracotta hover:bg-terracotta-50/80' }}">
                        Laporan Backorder
                    </a>
                    <a href="{{ route('delivery-orders.index') }}" class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('delivery-orders.*') ? 'bg-forest text-white shadow-glass-sm' : 'text-cream-600 hover:text-cream-900 hover:bg-white/80' }}">
                        Logistik (DO)
                    </a>
                    <a href="{{ route('purchase-orders.index') }}" class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('purchase-orders.*') ? 'bg-forest text-white shadow-glass-sm' : 'text-cream-600 hover:text-cream-900 hover:bg-white/80' }}">
                        Pengadaan (PO)
                    </a>
                    <a href="{{ route('goods-receipts.index') }}" class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('goods-receipts.*') ? 'bg-forest text-white shadow-glass-sm' : 'text-cream-600 hover:text-cream-900 hover:bg-white/80' }}">
                        Penerimaan (GRN)
                    </a>
                    <a href="{{ route('invoices.index') }}" class="px-3 py-2 rounded-xl transition-all {{ request()->routeIs('invoices.*') ? 'bg-forest text-white shadow-glass-sm' : 'text-cream-600 hover:text-cream-900 hover:bg-white/80' }}">
                        Faktur & Keuangan
                    </a>
                </nav>

                <!-- User Profile, PWA Install & Quick Role Switcher -->
                <div class="flex items-center space-x-2.5">
                    <!-- PWA Install Button (Header) -->
                    <div x-data="pwaInstaller()" x-cloak x-show="canInstall" class="inline-flex">
                        <button @click="installApp()" type="button"
                                class="glass-btn px-2.5 sm:px-3 py-1.5 text-xs font-semibold text-emerald-800 bg-emerald-50/90 hover:bg-emerald-100/90 border border-emerald-300/80 rounded-xl flex items-center space-x-1.5 shadow-sm transition-all hover:scale-105 active:scale-95"
                                title="Pasang aplikasi ERP Prima Jaya ke perangkat Anda">
                            <svg class="w-4 h-4 text-emerald-700 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span class="hidden sm:inline">Install App</span>
                            <span class="sm:hidden font-mono text-[11px]">Install</span>
                        </button>

                        <!-- iOS Safari Installation Guide Modal -->
                        <div x-show="showIOSGuide" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-md flex items-center justify-center p-4">
                            <div @click.away="showIOSGuide = false" class="glass-card bg-white/95 max-w-sm w-full p-6 space-y-4 shadow-glass-lg rounded-3xl border border-white/90 text-center text-xs">
                                <div class="w-12 h-12 rounded-2xl bg-forest-800 text-white font-mono font-bold text-lg flex items-center justify-center mx-auto shadow-md">
                                    PJ
                                </div>
                                <h3 class="text-sm font-bold text-cream-900">Pasang ERP Prima Jaya di iPhone/iPad</h3>
                                <p class="text-cream-600 leading-relaxed text-left space-y-1">
                                    1. Ketuk tombol <b>Bagikan (Share)</b> pada bilah Safari.<br>
                                    2. Gulir lalu pilih <b>"Tambahkan ke Layar Utama" (Add to Home Screen)</b>.<br>
                                    3. Ketuk <b>Tambah</b> di pojok kanan atas.
                                </p>
                                <button @click="showIOSGuide = false" type="button" class="glass-btn-primary w-full py-2.5 font-semibold text-xs rounded-xl">
                                    Mengerti
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Switch Role Dropdown -->
                    <div class="relative" x-data="{ roleMenuOpen: false }">
                        <button @click="roleMenuOpen = !roleMenuOpen" type="button" class="glass-btn px-3 py-1.5 text-xs text-cream-800 flex items-center space-x-1.5 rounded-xl hover:border-forest/40">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="font-medium hidden sm:inline">{{ Auth::user()->role->name ?? 'User' }}</span>
                            <span class="font-medium sm:hidden">{{ Auth::user()->role->code ?? 'Role' }}</span>
                            <svg class="w-3.5 h-3.5 text-cream-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="roleMenuOpen" @click.away="roleMenuOpen = false" x-cloak
                             class="absolute right-0 mt-2 w-64 glass-card bg-white/95 p-2 z-50 text-xs shadow-glass-lg rounded-2xl border border-white/90">
                            <div class="px-3 py-2 border-b border-slate-100 mb-1">
                                <p class="text-[10px] uppercase font-mono tracking-wider text-slate-400">Ganti Akun Demo (1-Klik)</p>
                                <p class="font-semibold text-cream-900 truncate">{{ Auth::user()->name }}</p>
                            </div>
                            <div class="space-y-1">
                                <a href="{{ route('quick-login', 'ADMIN') }}" class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-slate-50 transition-colors {{ Auth::user()->hasRole('ADMIN') ? 'bg-slate-100 font-semibold' : '' }}">
                                    <span>Administrator / Owner</span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-200/80 text-slate-800">ADMIN</span>
                                </a>
                                <a href="{{ route('quick-login', 'MARKETING') }}" class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-slate-50 transition-colors {{ Auth::user()->hasRole('MARKETING') ? 'bg-slate-100 font-semibold' : '' }}">
                                    <span>Penjualan (Marketing)</span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">MARKETING</span>
                                </a>
                                <a href="{{ route('quick-login', 'PURCHASING') }}" class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-slate-50 transition-colors {{ Auth::user()->hasRole('PURCHASING') ? 'bg-slate-100 font-semibold' : '' }}">
                                    <span>Pengadaan (Purchasing)</span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">PURCHASING</span>
                                </a>
                                <a href="{{ route('quick-login', 'LOGISTIK') }}" class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-slate-50 transition-colors {{ Auth::user()->hasRole('LOGISTIK') ? 'bg-slate-100 font-semibold' : '' }}">
                                    <span>Gudang & Logistik</span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-blue-100 text-blue-800">LOGISTIK</span>
                                </a>
                                <a href="{{ route('quick-login', 'ACCOUNTING') }}" class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-slate-50 transition-colors {{ Auth::user()->hasRole('ACCOUNTING') ? 'bg-slate-100 font-semibold' : '' }}">
                                    <span>Keuangan & Akuntansi</span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-purple-100 text-purple-800">ACCOUNTING</span>
                                </a>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-3 py-1.5 text-rose-600 hover:bg-rose-50 rounded-xl transition-colors font-medium">
                                        Keluar dari Sistem
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Logout Icon -->
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit" title="Keluar" class="glass-btn p-2 text-cream-500 hover:text-rose-600 rounded-xl">
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
            <div class="glass-card-sm bg-emerald-50/90 border border-emerald-200/80 p-4 rounded-2xl flex items-start space-x-3 text-sm text-emerald-900 mb-4 animate-fade-in shadow-glass-sm" role="alert">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="flex-1 font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="glass-card-sm bg-rose-50/90 border border-rose-200/80 p-4 rounded-2xl flex items-start space-x-3 text-sm text-rose-900 mb-4 animate-fade-in shadow-glass-sm" role="alert">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="flex-1 font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="glass-card-sm bg-rose-50/90 border border-rose-200/80 p-4 rounded-2xl text-sm text-rose-900 mb-4 shadow-glass-sm" role="alert">
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
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-5">
        @yield('content')
    </main>

    <!-- Mobile Floating PWA Install Banner -->
    <div x-data="pwaInstaller()" x-cloak x-show="canInstall && !dismissed" 
         class="md:hidden fixed bottom-20 left-4 right-4 z-40 glass-card bg-white/95 p-3.5 border border-white/90 shadow-glass-lg rounded-2xl flex items-center justify-between gap-3">
        <div class="flex items-center space-x-2.5">
            <div class="w-9 h-9 rounded-xl bg-forest-800 text-white font-mono font-bold text-sm flex items-center justify-center shrink-0 shadow-sm">
                PJ
            </div>
            <div>
                <h4 class="text-xs font-bold text-cream-900 leading-tight">Pasang ERP Prima Jaya</h4>
                <p class="text-[10px] text-cream-600">Akses instan satu-ketuk & mode offline di HP Anda.</p>
            </div>
        </div>
        <div class="flex items-center space-x-1.5 shrink-0">
            <button @click="installApp()" type="button" class="glass-btn-primary px-3 py-1.5 text-xs font-semibold rounded-xl shadow-sm">
                Install
            </button>
            <button @click="dismissBanner()" type="button" class="glass-btn p-1.5 text-cream-400 hover:text-cream-700 rounded-xl" title="Tutup">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <!-- Mobile Bottom Navigation (Luxury Frosted White Glass) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/80 backdrop-blur-xl border-t border-white/90 shadow-glass-lg px-2 py-2">
        <div class="grid grid-cols-5 gap-1 text-center">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'text-forest font-semibold scale-105' : 'text-cream-500 hover:text-cream-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="text-[10px] mt-0.5">Beranda</span>
            </a>
            <a href="{{ route('sales-orders.index') }}" class="flex flex-col items-center py-1 rounded-xl transition-all {{ request()->routeIs('sales-orders.*') ? 'text-forest font-semibold scale-105' : 'text-cream-500 hover:text-cream-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span class="text-[10px] mt-0.5">Penjualan</span>
            </a>
            <a href="{{ route('backorders.index') }}" class="flex flex-col items-center py-1 rounded-xl transition-all {{ request()->routeIs('backorders.*') ? 'text-terracotta font-semibold scale-105' : 'text-cream-500 hover:text-cream-800' }}">
                <div class="relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-[10px] mt-0.5">Backorder</span>
            </a>
            <a href="{{ route('delivery-orders.index') }}" class="flex flex-col items-center py-1 rounded-xl transition-all {{ request()->routeIs('delivery-orders.*') ? 'text-forest font-semibold scale-105' : 'text-cream-500 hover:text-cream-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                <span class="text-[10px] mt-0.5">Logistik</span>
            </a>
            <a href="{{ route('invoices.index') }}" class="flex flex-col items-center py-1 rounded-xl transition-all {{ request()->routeIs('invoices.*') ? 'text-forest font-semibold scale-105' : 'text-cream-500 hover:text-cream-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                <span class="text-[10px] mt-0.5">Faktur</span>
            </a>
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
