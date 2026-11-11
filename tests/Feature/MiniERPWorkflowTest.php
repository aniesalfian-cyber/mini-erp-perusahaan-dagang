<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AccountingService;
use App\Services\CreditCheckService;
use App\Services\DocumentNumberService;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MiniERPWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $marketing;
    protected User $logistik;
    protected User $purchasing;
    protected User $accounting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->admin = User::where('email', 'admin@primajaya.com')->firstOrFail();
        $this->marketing = User::where('email', 'marketing@primajaya.com')->firstOrFail();
        $this->logistik = User::where('email', 'logistik@primajaya.com')->firstOrFail();
        $this->purchasing = User::where('email', 'purchasing@primajaya.com')->firstOrFail();
        $this->accounting = User::where('email', 'accounting@primajaya.com')->firstOrFail();
    }

    public function test_login_and_quick_switch(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('ERP System Prima Jaya');

        $quickResponse = $this->get('/quick-login/MARKETING');
        $quickResponse->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->marketing);
    }

    public function test_item_creation_with_7_parameters(): void
    {
        $itemData = [
            'code' => 'TEST-BRG-' . uniqid(),
            'name' => 'Cat Dasar Alkali Sealer 20L',
            'unit' => 'Pail',
            'cost_price' => 550000,
            'selling_price' => 620000,
            'notes' => 'Cat dasar anti alkali untuk tembok baru plesteran',
            'min_stock' => 15,
            'initial_stock' => 50,
        ];

        $response = $this->actingAs($this->admin)->post('/items', $itemData);
        $response->assertRedirect('/items');

        $this->assertDatabaseHas('items', [
            'code' => $itemData['code'],
            'name' => $itemData['name'],
            'unit' => 'Pail',
            'min_stock' => 15,
        ]);

        $item = Item::where('code', $itemData['code'])->firstOrFail();
        $this->assertNotNull($item->stockBalance);
        $this->assertEquals(50, (float)$item->stockBalance->on_hand);
        $this->assertEquals(50, (float)$item->stockBalance->available);
    }

    public function test_sales_order_and_credit_check_flow(): void
    {
        $customer = Customer::firstOrFail();
        $item = Item::with('stockBalance')->whereHas('stockBalance', function ($q) {
            $q->where('available', '>=', 20);
        })->firstOrFail();

        $initialAvailable = (float)$item->stockBalance->available;
        $initialReserved = (float)$item->stockBalance->reserved;

        $orderPayload = [
            'customer_id' => $customer->id,
            'order_date' => date('Y-m-d'),
            'notes' => 'Pesanan proyek renovasi unit ruko',
            'items' => [
                [
                    'item_id' => $item->id,
                    'qty' => 10,
                    'unit_price' => (float)$item->selling_price,
                ],
            ],
        ];

        $response = $this->actingAs($this->marketing)->post('/sales-orders', $orderPayload);
        $response->assertStatus(302);

        $item->stockBalance->refresh();
        $this->assertEquals($initialReserved + 10, (float)$item->stockBalance->reserved);
        $this->assertEquals($initialAvailable - 10, (float)$item->stockBalance->available);
    }

    public function test_delivery_order_and_partial_shipment(): void
    {
        $customer = Customer::firstOrFail();
        $item = Item::firstOrFail();

        // Siapkan SO dengan 20 unit
        $invService = app(InventoryService::class);
        $docService = app(DocumentNumberService::class);

        $invService->adjustStock($item->id, 100, 'Test setup', $this->admin->id);

        $soNo = $docService->generate('SO');
        $so = SalesOrder::create([
            'order_no' => $soNo,
            'order_date' => date('Y-m-d'),
            'customer_id' => $customer->id,
            'sales_user_id' => $this->marketing->id,
            'total_amount' => 20 * (float)$item->selling_price,
            'status' => 'CONFIRMED',
        ]);

        $soItem = $so->items()->create([
            'item_id' => $item->id,
            'qty_ordered' => 20,
            'qty_shipped' => 0,
            'unit_price' => $item->selling_price,
            'subtotal' => 20 * (float)$item->selling_price,
        ]);

        $invService->reserveStock($item->id, 20, $this->marketing->id, $soNo, $so->id);

        // Logistik kirim 8 unit parsial
        $doPayload = [
            'sales_order_id' => $so->id,
            'do_date' => date('Y-m-d'),
            'vehicle_no' => 'B 1234 TEST',
            'driver_name' => 'Pak Joko',
            'items' => [
                [
                    'so_item_id' => $soItem->id,
                    'qty_delivered' => 8,
                ],
            ],
        ];

        $response = $this->actingAs($this->logistik)->post('/delivery-orders', $doPayload);
        $response->assertStatus(302);

        $so->refresh();
        $soItem->refresh();

        $this->assertEquals('PARTIAL_SHIPPED', $so->status);
        $this->assertEquals(8, (float)$soItem->qty_shipped);
        $this->assertEquals(12, (float)$so->pending_backorder_qty);

        // Verifikasi mutasi dicatat di stock ledger
        $this->assertDatabaseHas('stock_ledger', [
            'item_id' => $item->id,
            'transaction_type' => 'SO_DELIVERY',
            'qty_out' => 8,
        ]);
    }

    public function test_purchase_order_and_goods_receipt_inspection(): void
    {
        $supplier = Supplier::firstOrFail();
        $item = Item::firstOrFail();

        $poPayload = [
            'supplier_id' => $supplier->id,
            'po_date' => date('Y-m-d'),
            'items' => [
                [
                    'item_id' => $item->id,
                    'qty' => 50,
                    'unit_cost' => (float)$item->cost_price,
                ],
            ],
        ];

        $this->actingAs($this->purchasing)->post('/purchase-orders', $poPayload);
        $po = PurchaseOrder::where('supplier_id', $supplier->id)->latest('id')->firstOrFail();
        $poItem = $po->items()->firstOrFail();

        $initialOnHand = (float)$item->stockBalance->on_hand;

        // Logistik terima 50 unit: 45 lolos, 5 rusak/cacat
        $grnPayload = [
            'purchase_order_id' => $po->id,
            'receipt_date' => date('Y-m-d'),
            'supplier_do_no' => 'SJ-TEST-999',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'qty_received' => 50,
                    'qty_accepted' => 45,
                    'qty_rejected' => 5,
                    'reject_reason' => '5 kantong robek akibat basah saat bongkar muat',
                ],
            ],
        ];

        $response = $this->actingAs($this->logistik)->post('/goods-receipts', $grnPayload);
        $response->assertStatus(302);

        $item->stockBalance->refresh();
        $this->assertEquals($initialOnHand + 45, (float)$item->stockBalance->on_hand);

        $this->assertDatabaseHas('goods_receipt_items', [
            'qty_accepted' => 45,
            'qty_rejected' => 5,
        ]);
    }

    public function test_invoice_creation_and_payment_flow(): void
    {
        $do = DeliveryOrder::whereDoesntHave('invoice')->first();
        if (!$do) {
            $this->test_delivery_order_and_partial_shipment();
            $do = DeliveryOrder::whereDoesntHave('invoice')->firstOrFail();
        }

        $customer = $do->customer;
        $initialCredit = (float)$customer->current_credit;

        // Accounting terbitkan faktur
        $invResponse = $this->actingAs($this->accounting)->post('/invoices/from-do', [
            'delivery_order_id' => $do->id,
            'due_days' => 30,
        ]);
        $invResponse->assertStatus(302);

        $invoice = SalesInvoice::where('delivery_order_id', $do->id)->firstOrFail();
        $this->assertEquals('UNPAID', $invoice->payment_status);

        $customer->refresh();
        $this->assertEquals($initialCredit + (float)$invoice->total_amount, (float)$customer->current_credit);

        // Catat pembayaran parsial
        $halfAmount = round((float)$invoice->total_amount / 2, 2);
        $payResponse = $this->actingAs($this->accounting)->post("/invoices/{$invoice->id}/payment", [
            'amount' => $halfAmount,
            'payment_method' => 'BANK_TRANSFER',
            'reference_no' => 'BCA-TEST-881',
        ]);
        $payResponse->assertStatus(302);

        $invoice->refresh();
        $this->assertEquals('PARTIAL', $invoice->payment_status);

        // Pelunasan sisa
        $remaining = (float)$invoice->remaining_balance;
        $this->actingAs($this->accounting)->post("/invoices/{$invoice->id}/payment", [
            'amount' => $remaining,
            'payment_method' => 'CASH',
            'reference_no' => 'KASIR-001',
        ]);

        $invoice->refresh();
        $this->assertEquals('PAID', $invoice->payment_status);
        $this->assertEquals(0, (float)$invoice->remaining_balance);

        // Test export PDF
        $pdfResponse = $this->actingAs($this->accounting)->get("/invoices/{$invoice->id}/pdf");
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }
}
