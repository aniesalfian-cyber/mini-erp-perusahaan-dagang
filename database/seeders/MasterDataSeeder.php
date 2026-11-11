<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\DocumentSequence;
use App\Models\Item;
use App\Models\StockBalance;
use App\Models\StockLedger;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@primajaya.com')->first();
        $now = Carbon::now();

        // 1. Data Master Barang Toko Bangunan (7 Parameter Wajib)
        $itemsData = [
            [
                'code' => 'BRG-001',
                'name' => 'Semen Gresik PPC 50kg',
                'unit' => 'Sak',
                'cost_price' => 62000,
                'selling_price' => 68000,
                'notes' => 'Semen portland pozzolan untuk konstruksi umum dan plesteran',
                'min_stock' => 100,
                'initial_stock' => 350,
            ],
            [
                'code' => 'BRG-002',
                'name' => 'Semen Tiga Roda 50kg',
                'unit' => 'Sak',
                'cost_price' => 64000,
                'selling_price' => 70000,
                'notes' => 'Semen portland komposit mutu prima kuat tekan tinggi',
                'min_stock' => 100,
                'initial_stock' => 280,
            ],
            [
                'code' => 'BRG-003',
                'name' => 'Besi Beton Polos 8mm x 12m SNI',
                'unit' => 'Btg',
                'cost_price' => 48000,
                'selling_price' => 55000,
                'notes' => 'Besi behel standar SNI toleransi 0.3mm',
                'min_stock' => 200,
                'initial_stock' => 500,
            ],
            [
                'code' => 'BRG-004',
                'name' => 'Besi Beton Polos 10mm x 12m SNI',
                'unit' => 'Btg',
                'cost_price' => 76000,
                'selling_price' => 86000,
                'notes' => 'Besi tulangan struktur kolom dan balok rumah',
                'min_stock' => 200,
                'initial_stock' => 450,
            ],
            [
                'code' => 'BRG-005',
                'name' => 'Besi Beton Ulir 12mm x 12m SNI',
                'unit' => 'Btg',
                'cost_price' => 110000,
                'selling_price' => 125000,
                'notes' => 'Besi sirip/ulir daya lekat beton maksimal untuk gedung bertingkat',
                'min_stock' => 150,
                'initial_stock' => 320,
            ],
            [
                'code' => 'BRG-006',
                'name' => 'Bata Ringan / Hebel 10cm x 20cm x 60cm',
                'unit' => 'M3',
                'cost_price' => 580000,
                'selling_price' => 640000,
                'notes' => 'Autoclaved Aerated Concrete presisi tinggi kedap suara',
                'min_stock' => 30,
                'initial_stock' => 85,
            ],
            [
                'code' => 'BRG-007',
                'name' => 'Pipa PVC Wavin AW 1/2 inch x 4m',
                'unit' => 'Btg',
                'cost_price' => 28000,
                'selling_price' => 35000,
                'notes' => 'Pipa air bersih tekanan tinggi kelas AW anti pecah',
                'min_stock' => 50,
                'initial_stock' => 140,
            ],
            [
                'code' => 'BRG-008',
                'name' => 'Pipa PVC Wavin AW 3/4 inch x 4m',
                'unit' => 'Btg',
                'cost_price' => 38000,
                'selling_price' => 46000,
                'notes' => 'Pipa distribusi air bersih perumahan kelas AW',
                'min_stock' => 50,
                'initial_stock' => 120,
            ],
            [
                'code' => 'BRG-009',
                'name' => 'Pipa PVC Wavin D 3 inch x 4m',
                'unit' => 'Btg',
                'cost_price' => 125000,
                'selling_price' => 145000,
                'notes' => 'Pipa saluran pembuangan air kotor kelas D',
                'min_stock' => 30,
                'initial_stock' => 75,
            ],
            [
                'code' => 'BRG-010',
                'name' => 'Cat Tembok Dulux Weathershield Putih 20L',
                'unit' => 'Pail',
                'cost_price' => 1150000,
                'selling_price' => 1280000,
                'notes' => 'Cat dinding eksterior anti jamur dan tahan cuaca ekstrem',
                'min_stock' => 15,
                'initial_stock' => 40,
            ],
            [
                'code' => 'BRG-011',
                'name' => 'Cat Tembok Avitex Putih 25kg',
                'unit' => 'Pail',
                'cost_price' => 420000,
                'selling_price' => 475000,
                'notes' => 'Cat interior daya sebar luas cepat kering',
                'min_stock' => 20,
                'initial_stock' => 60,
            ],
            [
                'code' => 'BRG-012',
                'name' => 'Seng Gelombang BJLS 0.20mm x 1.8m',
                'unit' => 'Lbr',
                'cost_price' => 58000,
                'selling_price' => 68000,
                'notes' => 'Atap seng galvanis anti karat untuk gudang dan kanopi',
                'min_stock' => 100,
                'initial_stock' => 260,
            ],
            [
                'code' => 'BRG-013',
                'name' => 'Genteng Metal Pasir Merah 2x4',
                'unit' => 'Lbr',
                'cost_price' => 34000,
                'selling_price' => 41000,
                'notes' => 'Genteng metal lapis pasir peredam panas dan hujan',
                'min_stock' => 150,
                'initial_stock' => 380,
            ],
            [
                'code' => 'BRG-014',
                'name' => 'Granit Polished Glazed 60x60 Cream',
                'unit' => 'Dus',
                'cost_price' => 165000,
                'selling_price' => 190000,
                'notes' => 'Lantai granit homogen kilap tinggi 1 dus isi 4 keping (1.44m2)',
                'min_stock' => 50,
                'initial_stock' => 160,
            ],
            [
                'code' => 'BRG-015',
                'name' => 'Keramik Lantai 40x40 Putih Polos',
                'unit' => 'Dus',
                'cost_price' => 58000,
                'selling_price' => 67000,
                'notes' => 'Keramik lantai standar rumah subsidi 1 dus isi 6 keping (0.96m2)',
                'min_stock' => 80,
                'initial_stock' => 210,
            ],
            [
                'code' => 'BRG-016',
                'name' => 'Triplek Meranti 9mm x 122x244cm',
                'unit' => 'Lbr',
                'cost_price' => 115000,
                'selling_price' => 130000,
                'notes' => 'Kayu lapis meranti furniture dan plafon tahan lembab',
                'min_stock' => 40,
                'initial_stock' => 90,
            ],
            [
                'code' => 'BRG-017',
                'name' => 'Triplek Meranti 12mm x 122x244cm',
                'unit' => 'Lbr',
                'cost_price' => 155000,
                'selling_price' => 175000,
                'notes' => 'Plywood bekisting cor beton dan partisi ruangan',
                'min_stock' => 40,
                'initial_stock' => 80,
            ],
            [
                'code' => 'BRG-018',
                'name' => 'Kran Air Cabang Stainless SUS 304',
                'unit' => 'Pcs',
                'cost_price' => 85000,
                'selling_price' => 110000,
                'notes' => 'Kran shower ganda bahan stainless murni anti korosi',
                'min_stock' => 25,
                'initial_stock' => 65,
            ],
            [
                'code' => 'BRG-019',
                'name' => 'Kawat Bendrat Pengikat Besi SNI',
                'unit' => 'Roll',
                'cost_price' => 320000,
                'selling_price' => 360000,
                'notes' => 'Kawat hitam lunak pengikat anyaman behel 1 roll berat 20kg',
                'min_stock' => 15,
                'initial_stock' => 35,
            ],
            [
                'code' => 'BRG-020',
                'name' => 'Lem Pipa PVC Isarplas 400gr',
                'unit' => 'Klg',
                'cost_price' => 42000,
                'selling_price' => 50000,
                'notes' => 'Perekat senyawa pipa PVC daya rekat tinggi anti bocor',
                'min_stock' => 40,
                'initial_stock' => 110,
            ],
        ];

        foreach ($itemsData as $data) {
            $initialQty = $data['initial_stock'];
            unset($data['initial_stock']);

            $item = Item::firstOrCreate(['code' => $data['code']], $data);

            // Create initial stock balance
            StockBalance::firstOrCreate(
                ['item_id' => $item->id],
                [
                    'on_hand' => $initialQty,
                    'reserved' => 0,
                    'available' => $initialQty,
                ]
            );

            // Record initial stock in ledger
            StockLedger::firstOrCreate(
                [
                    'item_id' => $item->id,
                    'transaction_type' => 'INITIAL',
                ],
                [
                    'reference_type' => 'SYSTEM_INIT',
                    'reference_id' => $item->id,
                    'reference_no' => 'INIT-' . $item->code,
                    'qty_in' => $initialQty,
                    'qty_out' => 0,
                    'balance_after' => $initialQty,
                    'notes' => 'Saldo awal stok sistem peresmian ERP Prima Jaya',
                    'user_id' => $adminUser ? $adminUser->id : null,
                    'created_at' => $now,
                ]
            );
        }

        // 2. Data Master Pelanggan (4 Pelanggan dengan Plafon Kredit & Koordinat Peta)
        $customersData = [
            [
                'code' => 'CUST-001',
                'name' => 'PT Bangun Cipta Pratama',
                'phone' => '021-55443322',
                'address' => 'Jl. Daan Mogot No. 45, Kalideres, Jakarta Barat',
                'credit_limit' => 150000000,
                'current_credit' => 25000000,
                'latitude' => -6.16040000,
                'longitude' => 106.74560000,
                'is_active' => true,
            ],
            [
                'code' => 'CUST-002',
                'name' => 'CV Makmur Jaya Konstruksi',
                'phone' => '021-88776655',
                'address' => 'Jl. Raya Bekasi KM 22, Cakung, Jakarta Timur',
                'credit_limit' => 75000000,
                'current_credit' => 12000000,
                'latitude' => -6.19230000,
                'longitude' => 106.94210000,
                'is_active' => true,
            ],
            [
                'code' => 'CUST-003',
                'name' => 'Toko Besi & Material Subur Sentosa',
                'phone' => '021-77889900',
                'address' => 'Jl. Raya Margonda No. 102, Depok',
                'credit_limit' => 50000000,
                'current_credit' => 0,
                'latitude' => -6.37280000,
                'longitude' => 106.83220000,
                'is_active' => true,
            ],
            [
                'code' => 'CUST-004',
                'name' => 'Mandiri Developer Propertindo',
                'phone' => '021-29883344',
                'address' => 'Ruko Paramount Dotcom Blok B15, Gading Serpong, Tangerang',
                'credit_limit' => 200000000,
                'current_credit' => 45000000,
                'latitude' => -6.24150000,
                'longitude' => 106.62890000,
                'is_active' => true,
            ],
        ];

        foreach ($customersData as $cust) {
            Customer::firstOrCreate(['code' => $cust['code']], $cust);
        }

        // 3. Data Master Pemasok (4 Supplier Utama Bahan Bangunan)
        $suppliersData = [
            [
                'code' => 'SUPP-001',
                'name' => 'PT Semen Indonesia Distributor',
                'phone' => '021-83701234',
                'address' => 'Gedung Graha Irama Lt. 9, Jl. H.R. Rasuna Said, Jakarta Selatan',
                'contact_person' => 'Bapak Hendra (Sales Manager)',
                'latitude' => -6.22340000,
                'longitude' => 106.83100000,
                'is_active' => true,
            ],
            [
                'code' => 'SUPP-002',
                'name' => 'PT Krakatau Steel Perkasa Mandiri',
                'phone' => '0254-392111',
                'address' => 'Kawasan Industri Krakatau Steel Kav. 14, Cilegon, Banten',
                'contact_person' => 'Ibu Ratna (Divisi Distribusi)',
                'latitude' => -6.00920000,
                'longitude' => 106.01230000,
                'is_active' => true,
            ],
            [
                'code' => 'SUPP-003',
                'name' => 'PT Wahana Duta Jaya Rucika',
                'phone' => '021-3441234',
                'address' => 'Komplek Harmoni Plaza Blok J No. 1-4, Jakarta Pusat',
                'contact_person' => 'Pak Bambang (Area Sales)',
                'latitude' => -6.16230000,
                'longitude' => 106.81880000,
                'is_active' => true,
            ],
            [
                'code' => 'SUPP-004',
                'name' => 'PT AkzoNobel Paints Indonesia (Dulux)',
                'phone' => '021-8983000',
                'address' => 'Jababeka Industrial Estate Blok C No. 5, Cikarang',
                'contact_person' => 'Pak Surya (Key Account)',
                'latitude' => -6.30870000,
                'longitude' => 107.13900000,
                'is_active' => true,
            ],
        ];

        foreach ($suppliersData as $supp) {
            Supplier::firstOrCreate(['code' => $supp['code']], $supp);
        }

        // 4. Inisialisasi Sequence Penomoran Dokumen
        $prefixes = ['SO', 'DO', 'PO', 'GRN', 'INV', 'PAY'];
        $year = (int)$now->format('Y');
        $month = (int)$now->format('n');

        foreach ($prefixes as $p) {
            DocumentSequence::firstOrCreate(
                [
                    'prefix' => $p,
                    'year' => $year,
                    'month' => $month,
                ],
                [
                    'current_number' => 0,
                ]
            );
        }
    }
}
