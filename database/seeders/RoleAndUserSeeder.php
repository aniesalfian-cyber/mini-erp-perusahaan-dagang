<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'code' => 'ADMIN',
                'name' => 'Administrator / Owner',
                'description' => 'Akses penuh ke seluruh modul, master data, konfigurasi, dan laporan analitik eksekutif',
            ],
            [
                'code' => 'MARKETING',
                'name' => 'Divisi Penjualan (Marketing)',
                'description' => 'Input Sales Order / PO Masuk dari pelanggan, cek plafon kredit, dan pantau backorder',
            ],
            [
                'code' => 'PURCHASING',
                'name' => 'Divisi Pengadaan (Purchasing)',
                'description' => 'Penerbitan PO Keluar ke supplier, pantau estimasi kedatangan, dan histori harga beli',
            ],
            [
                'code' => 'LOGISTIK',
                'name' => 'Divisi Gudang & Logistik',
                'description' => 'Penerbitan Surat Jalan (DO), pengiriman parsial, inspeksi GRN barang masuk, dan kartu stok',
            ],
            [
                'code' => 'ACCOUNTING',
                'name' => 'Divisi Keuangan & Akuntansi',
                'description' => 'Penerbitan Faktur Penjualan (Invoice PDF), rekonsiliasi piutang, dan pencatatan pembayaran',
            ],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['code' => $r['code']], $r);
        }

        $adminRole = Role::where('code', 'ADMIN')->first();
        $marketingRole = Role::where('code', 'MARKETING')->first();
        $purchasingRole = Role::where('code', 'PURCHASING')->first();
        $logistikRole = Role::where('code', 'LOGISTIK')->first();
        $accountingRole = Role::where('code', 'ACCOUNTING')->first();

        $defaultPassword = Hash::make('Password123!');

        $users = [
            [
                'role_id' => $adminRole->id,
                'name' => 'Bapak Hendrawan (Owner)',
                'email' => 'admin@primajaya.com',
                'password' => $defaultPassword,
                'phone' => '081299887701',
                'is_active' => true,
            ],
            [
                'role_id' => $marketingRole->id,
                'name' => 'Budi Santoso (Marketing)',
                'email' => 'marketing@primajaya.com',
                'password' => $defaultPassword,
                'phone' => '081299887702',
                'is_active' => true,
            ],
            [
                'role_id' => $purchasingRole->id,
                'name' => 'Siti Aminah (Purchasing)',
                'email' => 'purchasing@primajaya.com',
                'password' => $defaultPassword,
                'phone' => '081299887703',
                'is_active' => true,
            ],
            [
                'role_id' => $logistikRole->id,
                'name' => 'Doni Setiawan (Logistik)',
                'email' => 'logistik@primajaya.com',
                'password' => $defaultPassword,
                'phone' => '081299887704',
                'is_active' => true,
            ],
            [
                'role_id' => $accountingRole->id,
                'name' => 'Rina Wijaya (Accounting)',
                'email' => 'accounting@primajaya.com',
                'password' => $defaultPassword,
                'phone' => '081299887705',
                'is_active' => true,
            ],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(['email' => $u['email']], $u);
        }
    }
}
