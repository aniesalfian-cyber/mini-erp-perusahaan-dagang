<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk | ERP System Prima Jaya</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#FFFFFF">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="text-cream-900 min-h-screen flex items-center justify-center p-4 sm:p-6 antialiased selection:bg-forest selection:text-white">

    <div class="w-full max-w-lg space-y-6">

        <!-- Header Brand with Luxury Glass Aura -->
        <div class="text-center space-y-2">
            <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-br from-forest-800 to-forest-600 items-center justify-center text-white font-mono font-bold text-2xl shadow-glass-lg border border-white/30 backdrop-blur-md">
                PJ
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-cream-900">ERP System Prima Jaya</h1>
            <p class="text-xs text-cream-600 max-w-xs mx-auto">
                Perusahaan Dagang & Distribusi Bahan Bangunan. Sistem Multi-Divisi: Marketing, Purchasing, Logistik, Accounting, Admin.
            </p>
        </div>

        <!-- Login Form Card (Luxury White Glassmorphism) -->
        <div class="glass-card p-6 sm:p-8 space-y-6 border border-white/90 bg-white/80 shadow-glass-lg">

            <!-- Flash Error -->
            @if ($errors->any())
                <div class="glass-inset p-3 text-xs text-rose-800 border border-rose-300/40 rounded-xl bg-rose-50/60">
                    <p class="font-semibold mb-1">Gagal Masuk:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('info'))
                <div class="glass-inset p-3 text-xs text-cream-700 border border-slate-200 rounded-xl">
                    {{ session('info') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-medium text-cream-700 mb-1.5">Email Pengguna</label>
                    <input type="email" id="email" name="email" value="{{ old('email', 'admin@primajaya.com') }}" required autofocus
                           class="glass-input w-full px-3.5 py-2.5 text-sm font-mono placeholder-cream-400">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-medium text-cream-700">Kata Sandi</label>
                        <span class="text-[11px] font-mono text-cream-500">Default: Password123!</span>
                    </div>
                    <input type="password" id="password" name="password" value="Password123!" required
                           class="glass-input w-full px-3.5 py-2.5 text-sm font-mono placeholder-cream-400">
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center space-x-2 text-xs text-cream-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-forest focus:ring-forest">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="glass-btn-primary w-full py-3 text-sm font-semibold tracking-wide shadow-md">
                    Masuk ke Sistem
                </button>
            </form>

            <!-- 1-Click Fast Role Switcher for Testing & Demonstration -->
            <div class="pt-4 border-t border-slate-200/80">
                <p class="text-[11px] font-mono uppercase tracking-wider text-cream-500 text-center mb-3">
                    Akses Cepat Demo Sesuai Divisi (1-Klik)
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-left">
                    <a href="{{ route('quick-login', 'ADMIN') }}" class="glass-btn p-2.5 rounded-xl hover:border-forest/50 transition-all flex items-center space-x-2.5 group">
                        <div class="w-7 h-7 rounded-lg bg-forest/15 text-forest flex items-center justify-center font-bold text-xs group-hover:bg-forest group-hover:text-white transition-colors">
                            A
                        </div>
                        <div class="truncate">
                            <span class="text-xs font-semibold text-cream-900 block truncate">Administrator / Owner</span>
                            <span class="text-[10px] text-cream-500 block truncate">admin@primajaya.com</span>
                        </div>
                    </a>

                    <a href="{{ route('quick-login', 'MARKETING') }}" class="glass-btn p-2.5 rounded-xl hover:border-forest/50 transition-all flex items-center space-x-2.5 group">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            M
                        </div>
                        <div class="truncate">
                            <span class="text-xs font-semibold text-cream-900 block truncate">Marketing (Penjualan)</span>
                            <span class="text-[10px] text-cream-500 block truncate">marketing@primajaya.com</span>
                        </div>
                    </a>

                    <a href="{{ route('quick-login', 'PURCHASING') }}" class="glass-btn p-2.5 rounded-xl hover:border-forest/50 transition-all flex items-center space-x-2.5 group">
                        <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs group-hover:bg-amber-600 group-hover:text-white transition-colors">
                            P
                        </div>
                        <div class="truncate">
                            <span class="text-xs font-semibold text-cream-900 block truncate">Purchasing (Pengadaan)</span>
                            <span class="text-[10px] text-cream-500 block truncate">purchasing@primajaya.com</span>
                        </div>
                    </a>

                    <a href="{{ route('quick-login', 'LOGISTIK') }}" class="glass-btn p-2.5 rounded-xl hover:border-forest/50 transition-all flex items-center space-x-2.5 group">
                        <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-800 flex items-center justify-center font-bold text-xs group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            L
                        </div>
                        <div class="truncate">
                            <span class="text-xs font-semibold text-cream-900 block truncate">Gudang & Logistik</span>
                            <span class="text-[10px] text-cream-500 block truncate">logistik@primajaya.com</span>
                        </div>
                    </a>

                    <a href="{{ route('quick-login', 'ACCOUNTING') }}" class="glass-btn p-2.5 rounded-xl hover:border-forest/50 transition-all flex items-center space-x-2.5 sm:col-span-2 group">
                        <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-800 flex items-center justify-center font-bold text-xs group-hover:bg-purple-600 group-hover:text-white transition-colors">
                            K
                        </div>
                        <div class="truncate">
                            <span class="text-xs font-semibold text-cream-900 block truncate">Accounting (Keuangan & Faktur)</span>
                            <span class="text-[10px] text-cream-500 block truncate">accounting@primajaya.com</span>
                        </div>
                    </a>
                </div>
            </div>

        </div>

        <!-- PWA Installation Card (Login View) -->
        <div x-data="pwaInstaller()" x-cloak x-show="canInstall" class="glass-card p-4 border border-white/90 bg-white/80 shadow-glass-sm flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="flex items-center space-x-3 text-left">
                <div class="w-10 h-10 rounded-xl bg-forest-800 text-white font-mono font-bold flex items-center justify-center shrink-0 shadow-sm">
                    PJ
                </div>
                <div>
                    <h4 class="font-bold text-cream-900 text-xs">Gunakan Aplikasi ERP Prima Jaya</h4>
                    <p class="text-[11px] text-cream-600">Pasang langsung ke ponsel atau komputer tanpa App Store / Play Store.</p>
                </div>
            </div>

            <button @click="installApp()" type="button" class="glass-btn-primary w-full sm:w-auto px-4 py-2 font-semibold flex items-center justify-center space-x-2 rounded-xl shadow-sm hover:scale-105 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Install Aplikasi Sekarang</span>
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

        <!-- Footer Note -->
        <p class="text-center text-[11px] text-cream-500 font-mono">
            &copy; {{ date('Y') }} ERP System Prima Jaya. Luxury White Glassmorphism Edition.
        </p>

    </div>

</body>
</html>
