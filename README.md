# ERP System Prima Jaya (Trading & Distribution ERP)

Sistem ERP Perusahaan Dagang & Distribusi Bahan Bangunan terintegrasi dari hulu ke hilir (Order-to-Cash & Procure-to-Pay), dirancang khusus untuk operasional multi-divisi dengan antarmuka neomorphic mobile-first ramah sentuhan, PWA offline support, dan validasi integritas transaksi presisi tinggi.

---

## URL Akses Produksi

- **Aplikasi Web Live:** [https://erp-primajaya.harvestdigital.pro](https://erp-primajaya.harvestdigital.pro)
- **Spesifikasi PRD:** [https://harvestdigital.pro/PRD.md](https://harvestdigital.pro/PRD.md)
- **Mandor Roadmap (TODO):** [https://harvestdigital.pro/TODO.md](https://harvestdigital.pro/TODO.md)

---

## Tech Stack Utama

1. **Backend:** Laravel 11.x (PHP 8.3+) dengan arsitektur Repository / Service Pattern (`app/Services`, `app/Repositories`).
2. **Frontend:** Blade + Tailwind CSS + Alpine.js (animasi liquid fluid) + Vite bundling.
3. **PWA Engine:** Service Worker (`sw.js`), Manifest JSON, Offline Support Caching, Background Sync.
4. **Database:** MySQL 8.0 (InnoDB Strict Decimal `DECIMAL(14,2)`, Foreign Key Integrity, Spatial Coordinates).
5. **Pustaka Tambahan:**
   - `leaflet.js` & `leaflet-routing-machine`: Peta interaktif sebaran lokasi pelanggan dan pemasok.
   - `chart.js` + `chartjs-plugin-datalabels`: Visualisasi tren omzet bulanan pada dashboard.
   - `sweetalert2`: Notifikasi & dialog konfirmasi aksi.
   - `barryvdh/laravel-dompdf`: Generator Faktur Penjualan (Invoice PDF) siap cetak.
   - `maatwebsite/excel`: Dukungan ekspor laporan data.

---

## 5 Divisi & Hak Akses Pengguna (Default Demo Login)

Sistem dilengkapi tombol pintas **1-Klik Ganti Akun Demo** di pojok kanan atas aplikasi untuk mempermudah evaluasi:

| Divisi | Nama Akun | Email Login | Password | Tanggung Jawab Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Admin** | Bapak Hendrawan (Owner) | `admin@primajaya.com` | `Password123!` | Akses penuh seluruh modul, analitik keuangan, stok opname |
| **Marketing** | Budi Santoso | `marketing@primajaya.com` | `Password123!` | Input Sales Order (PO Masuk), cek plafon kredit, monitor backorder |
| **Purchasing** | Siti Aminah | `purchasing@primajaya.com` | `Password123!` | Penerbitan Purchase Order ke pemasok, histori harga beli |
| **Logistik** | Doni Setiawan | `logistik@primajaya.com` | `Password123!` | Surat Jalan (DO), pengiriman parsial, inspeksi barang masuk (GRN) |
| **Accounting** | Rina Wijaya | `accounting@primajaya.com` | `Password123!` | Penerbitan Faktur Penjualan (PDF), kontrol piutang, pelunasan kasir |

---

## Fitur Unggulan Sistem

1. **Master Data Barang (7 Parameter Wajib):**
   - Kode Barang (SKU), Nama Barang, Satuan, Harga Beli, Harga Jual, Keterangan, dan Stok Minimum.
   - Dilengkapi kartu stok riwayat mutasi (*append-only ledger*) dan fitur stok opname Admin.

2. **Siklus Penjualan & Proteksi Plafon Kredit:**
   - Validasi limit kredit pelanggan secara otomatis sebelum pesanan disetujui.
   - Penguncian kuantitas reservasi barang (*Reserved Stock*) agar tidak terjadi *overselling*.

3. **Logistik Gudang & Pengiriman Parsial:**
   - Penerbitan Surat Jalan (Delivery Order) bertahap sesuai ketersediaan armada.
   - Pemotongan otomatis saldo fisik (*On-Hand*) dan pelepasan alokasi (*Reserved*).

4. **Laporan Khusus: Barang PO Belum Dikirimkan (Outstanding Backorder):**
   - Pemantauan real-time sisa kuantitas pesanan yang belum terkirim ke konsumen.
   - Indikator umur pesanan (hari) dan peringatan kesiapan stok fisik gudang.
   - Fitur ekspor laporan ke format file CSV / Excel.

5. **Pengadaan & Inspeksi Kualitas Barang Masuk (Goods Receipt / GRN):**
   - Penerimaan barang pesanan dari supplier dengan inspeksi barang lolos vs cacat (*rejected*).
   - Pencatatan alasan penolakan dan penambahan otomatis stok gudang untuk barang lolos.

6. **Keuangan & Cetak Faktur PDF:**
   - Penerbitan Faktur Penjualan otomatis dari Surat Jalan yang sah.
   - Cetak dokumen PDF resmi menggunakan Dompdf lengkap dengan detail rekening transfer dan kolom tanda tangan pengesahan.
   - Pencatatan pembayaran cicilan atau lunas, otomatis mengurangi piutang berjalan pelanggan.

---

## Panduan Instalasi Lokal / Server

```bash
# 1. Kloning Repositori
git clone https://github.com/aniesalfian-cyber/mini-erp-perusahaan-dagang.git
cd mini-erp-perusahaan-dagang

# 2. Pasang Dependensi Backend & Frontend
composer install
npm install
npm run build

# 3. Konfigurasi Environment
cp .env.example .env
php artisan key:generate

# 4. Migrasi & Seeder Database
php artisan migrate --seed --force

# 5. Buat Symlink Storage
php artisan storage:link

# 6. Jalankan Server
php artisan serve
```

---

## Lisensi & Kepatuhan

Dikembangkan untuk **PT Prima Jaya Perkasa**. Seluruh kode mematuhi standar Clean Architecture, ACID Database Transactions, dan pedoman Anti-Slop.
