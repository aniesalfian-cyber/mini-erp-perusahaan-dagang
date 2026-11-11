<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dokumen Transaksi') | PT Prima Jaya Material</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 14mm 14mm 14mm;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #0f172a !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 11px !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            tr {
                page-break-inside: avoid;
            }
            .signature-block {
                page-break-inside: avoid;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: #0f172a;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-6 sm:py-10 text-slate-800 antialiased selection:bg-emerald-500 selection:text-white">

    <!-- Top Action Bar for Screen Viewing (Hidden during printing) -->
    <header class="no-print max-w-4xl mx-auto mb-6 px-4">
        <div class="bg-white/90 backdrop-blur-md border border-slate-200/80 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-forest-800 text-white flex items-center justify-center font-bold font-mono text-sm shadow-sm">
                    PJ
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Format Cetak Dokumen Sah Perusahaan</h2>
                    <p class="text-xs text-slate-500">Gunakan kertas A4 Portrait untuk hasil cetak atau ekspor PDF presisi tinggi.</p>
                </div>
            </div>

            <div class="flex items-center space-x-2.5 w-full sm:w-auto">
                <button type="button" onclick="window.print()" class="flex-1 sm:flex-none inline-flex items-center justify-center space-x-2 px-5 py-2.5 bg-forest-800 hover:bg-forest-900 text-white text-xs font-semibold rounded-xl shadow transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Sekarang (Print)</span>
                </button>

                <button type="button" onclick="window.close(); if(!window.closed) window.history.back();" class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-xl transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </header>

    <!-- Main Printable Sheet -->
    <main class="print-container max-w-4xl mx-auto bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-12 shadow-md">
        
        <!-- Official Company Letterhead (KOP SURAT) -->
        <header class="border-b-2 border-forest-900 pb-4 mb-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 rounded-2xl bg-forest-800 text-white flex items-center justify-center font-bold font-mono text-xl shadow-sm border border-forest-700">
                        PJ
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-forest-900 uppercase">
                            PT Prima Jaya Material
                        </h1>
                        <p class="text-xs font-semibold text-slate-700">
                            Distributor Utama Bahan Bangunan, Semen, Besi Baja, Pipa & Cat Konstruksi
                        </p>
                        <p class="text-[11px] text-slate-500 leading-relaxed mt-0.5">
                            Kawasan Industri Pergudangan Prima Blok C12 No. 8-10, Tangerang, Banten 15143<br>
                            Telp: (021) 5566-7890 | WA: 0812-9876-5432 | Email: billing@primajaya.com | NPWP: 01.234.567.8-412.000
                        </p>
                    </div>
                </div>

                <div class="text-right hidden sm:block">
                    <div class="inline-block px-3 py-1 rounded bg-slate-100 text-[10px] font-mono text-slate-600 uppercase tracking-widest border border-slate-200">
                        Dokumen Asli
                    </div>
                    <div class="text-[10px] text-slate-400 font-mono mt-1">
                        erp-primajaya.harvestdigital.pro
                    </div>
                </div>
            </div>
            <div class="h-0.5 bg-forest-700 mt-2"></div>
        </header>

        <!-- Document Body Slot -->
        @yield('content')

        <!-- Official Footer -->
        <footer class="mt-8 pt-4 border-t border-slate-200 text-[10px] text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                Dicetak melalui ERP Prima Jaya pada {{ date('d/m/Y H:i:s') }} WIB
            </div>
            <div class="font-mono">
                Lembar Sah Perusahaan | Harap simpan dokumen ini sebagai arsip resmi.
            </div>
        </footer>

    </main>

</body>
</html>
