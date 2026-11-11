<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesInvoicePayment;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AccountingService;
use App\Services\DocumentNumberService;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@primajaya.com')->firstOrFail();
        $marketing = User::where('email', 'marketing@primajaya.com')->firstOrFail();
        $purchasing = User::where('email', 'purchasing@primajaya.com')->firstOrFail();
        $logistik = User::where('email', 'logistik@primajaya.com')->firstOrFail();
        $accounting = User::where('email', 'accounting@primajaya.com')->firstOrFail();

        $docService = app(DocumentNumberService::class);
        $inventoryService = app(InventoryService::class);
        $accountingService = app(AccountingService::class);

        $customer1 = Customer::where('code', 'CUST-001')->first() ?? Customer::first();
        $customer2 = Customer::where('code', 'CUST-002')->first() ?? Customer::first();
        $supplier1 = Supplier::where('code', 'SUPP-001')->first() ?? Supplier::first();
        $supplier2 = Supplier::where('code', 'SUPP-002')->first() ?? Supplier::first();

        $itemSemen = Item::where('code', 'BRG-001')->first() ?? Item::first();
        $itemBesi = Item::where('code', 'BRG-003')->first() ?? Item::skip(1)->first();
        $itemBata = Item::where('code', 'BRG-005')->first() ?? Item::skip(2)->first();

        // 1. Buat Purchase Order dan Penerimaan Barang (GRN)
        if (PurchaseOrder::count() === 0) {
            DB::transaction(function () use ($docService, $inventoryService, $purchasing, $logistik, $supplier1, $itemSemen, $itemBesi) {
                $poNo = $docService->generate('PO');
                $po = PurchaseOrder::create([
                    'po_no' => $poNo,
                    'supplier_id' => $supplier1->id,
                    'purchasing_user_id' => $purchasing->id,
                    'po_date' => Carbon::now()->subDays(5),
                    'expected_date' => Carbon::now()->addDays(2),
                    'status' => 'COMPLETED',
                    'total_amount' => (50 * (float)$itemSemen->cost_price) + (100 * (float)$itemBesi->cost_price),
                    'notes' => 'Pengadaan reguler stok semen dan besi beton SNI proyek perumahan',
                ]);

                $poItem1 = PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'item_id' => $itemSemen->id,
                    'qty_ordered' => 50,
                    'qty_received' => 50,
                    'unit_cost' => $itemSemen->cost_price,
                    'subtotal' => 50 * (float)$itemSemen->cost_price,
                ]);

                $poItem2 = PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'item_id' => $itemBesi->id,
                    'qty_ordered' => 100,
                    'qty_received' => 100,
                    'unit_cost' => $itemBesi->cost_price,
                    'subtotal' => 100 * (float)$itemBesi->cost_price,
                ]);

                // Penerimaan Barang (GRN)
                $grnNo = $docService->generate('GRN');
                $grn = GoodsReceipt::create([
                    'grn_no' => $grnNo,
                    'purchase_order_id' => $po->id,
                    'supplier_id' => $supplier1->id,
                    'logistics_user_id' => $logistik->id,
                    'receipt_date' => Carbon::now()->subDays(3),
                    'supplier_do_no' => 'SJ-SG-99812',
                    'status' => 'ACCEPTED',
                    'notes' => 'Bongkar muat kontainer pabrik di dermaga gudang B, kondisi kemasan prima',
                ]);

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $grn->id,
                    'purchase_order_item_id' => $poItem1->id,
                    'item_id' => $itemSemen->id,
                    'qty_received' => 50,
                    'qty_accepted' => 48,
                    'qty_rejected' => 2,
                    'reject_reason' => '2 kantong sak sobek di sudut pallet bawah',
                ]);

                $inventoryService->receiveStock($itemSemen->id, 48, $logistik->id, $grnNo, $grn->id, 'Penerimaan GRN ' . $grnNo);

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $grn->id,
                    'purchase_order_item_id' => $poItem2->id,
                    'item_id' => $itemBesi->id,
                    'qty_received' => 100,
                    'qty_accepted' => 100,
                    'qty_rejected' => 0,
                    'reject_reason' => null,
                ]);

                $inventoryService->receiveStock($itemBesi->id, 100, $logistik->id, $grnNo, $grn->id, 'Penerimaan GRN ' . $grnNo);
            });
        }

        // 2. Buat Sales Order, Surat Jalan (DO), dan Faktur Penjualan (INV)
        if (DeliveryOrder::count() === 0) {
            DB::transaction(function () use ($docService, $inventoryService, $accountingService, $marketing, $logistik, $accounting, $customer1, $itemSemen, $itemBesi) {
                // Sales Order
                $soNo = $docService->generate('SO');
                $totalSO = (30 * (float)$itemSemen->selling_price) + (50 * (float)$itemBesi->selling_price);

                $so = SalesOrder::create([
                    'order_no' => $soNo,
                    'customer_id' => $customer1->id,
                    'sales_user_id' => $marketing->id,
                    'order_date' => Carbon::now()->subDays(2),
                    'status' => 'COMPLETED',
                    'total_amount' => $totalSO,
                    'notes' => 'Pesanan material pengecoran ruko 3 lantai Blok A',
                ]);

                $soItem1 = SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'item_id' => $itemSemen->id,
                    'qty_ordered' => 30,
                    'qty_shipped' => 30,
                    'unit_price' => $itemSemen->selling_price,
                    'subtotal' => 30 * (float)$itemSemen->selling_price,
                ]);

                $soItem2 = SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'item_id' => $itemBesi->id,
                    'qty_ordered' => 50,
                    'qty_shipped' => 50,
                    'unit_price' => $itemBesi->selling_price,
                    'subtotal' => 50 * (float)$itemBesi->selling_price,
                ]);

                // Surat Jalan (Delivery Order)
                $doNo = $docService->generate('DO');
                $do = DeliveryOrder::create([
                    'do_no' => $doNo,
                    'sales_order_id' => $so->id,
                    'customer_id' => $customer1->id,
                    'logistics_user_id' => $logistik->id,
                    'do_date' => Carbon::now()->subDay(),
                    'delivery_status' => 'DELIVERED',
                    'driver_name' => 'Bambang Supriyanto',
                    'vehicle_no' => 'B 9281 PJ',
                    'recipient_name' => 'Pak Joko (Mandor Proyek)',
                    'delivered_at' => Carbon::now()->subHours(10),
                    'notes' => 'Barang tiba lengkap dan diturunkan langsung di samping gudang proyek',
                ]);

                DeliveryOrderItem::create([
                    'delivery_order_id' => $do->id,
                    'sales_order_item_id' => $soItem1->id,
                    'item_id' => $itemSemen->id,
                    'qty_delivered' => 30,
                ]);

                $inventoryService->shipStock($itemSemen->id, 30, $logistik->id, $doNo, $do->id, 'Pengiriman DO ' . $doNo);

                DeliveryOrderItem::create([
                    'delivery_order_id' => $do->id,
                    'sales_order_item_id' => $soItem2->id,
                    'item_id' => $itemBesi->id,
                    'qty_delivered' => 50,
                ]);

                $inventoryService->shipStock($itemBesi->id, 50, $logistik->id, $doNo, $do->id, 'Pengiriman DO ' . $doNo);

                // Faktur Penjualan (Sales Invoice)
                $invoice = $accountingService->createInvoiceFromDO($do, $accounting, 30);

                // Catat pembayaran uang muka 50%
                $dpAmount = round((float)$invoice->total_amount * 0.5, 2);
                $accountingService->recordPayment(
                    $invoice,
                    $dpAmount,
                    'BANK_TRANSFER',
                    'BCA-88912-INV',
                    $accounting,
                    'Uang muka pelunasan material proyek'
                );
            });
        }
    }
}
