<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Payment;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AccountingService
{
    public function __construct(
        protected DocumentNumberService $docNumberService,
        protected CreditCheckService $creditCheckService,
        protected AuditTrailService $auditTrailService
    ) {}

    /**
     * Terbitkan Faktur Penjualan (Invoice) dari Surat Jalan sah (Delivery Order).
     */
    public function createInvoiceFromDO(DeliveryOrder $do, User $user, int $dueDays = 30): SalesInvoice
    {
        return DB::transaction(function () use ($do, $user, $dueDays) {
            $invoiceNo = $this->docNumberService->generate('INV');
            $today = Carbon::today();
            $dueDate = Carbon::today()->addDays($dueDays);

            $so = $do->salesOrder;
            $customer = $do->customer;

            // Hitung subtotal dari item DO dengan harga satuan pada SO
            $subtotal = 0;
            $itemsData = [];

            foreach ($do->items as $doItem) {
                $soItem = $doItem->salesOrderItem;
                $unitPrice = $soItem ? (float)$soItem->unit_price : (float)$doItem->item->selling_price;
                $itemSubtotal = (float)$doItem->qty_delivered * $unitPrice;
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'item_id' => $doItem->item_id,
                    'description' => "{$doItem->item->name} ({$doItem->item->code})",
                    'qty' => $doItem->qty_delivered,
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                ];
            }

            // PPN 11% opsional (default 0 atau disesuaikan)
            $taxAmount = 0;
            $totalAmount = $subtotal + $taxAmount;

            $invoice = SalesInvoice::create([
                'invoice_no' => $invoiceNo,
                'invoice_date' => $today,
                'due_date' => $dueDate,
                'sales_order_id' => $so->id,
                'delivery_order_id' => $do->id,
                'customer_id' => $customer->id,
                'accounting_user_id' => $user->id,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'payment_status' => 'UNPAID',
                'notes' => "Faktur diterbitkan atas Surat Jalan {$do->do_no}",
            ]);

            foreach ($itemsData as $row) {
                $invoice->items()->create($row);
            }

            // Tambah piutang berjalan pada customer
            $this->creditCheckService->increaseCredit($customer->id, $totalAmount);

            // Generate initial PDF invoice
            $this->generatePdf($invoice);

            $this->auditTrailService->log(
                'CREATE_INVOICE',
                'sales_invoices',
                $invoice->id,
                null,
                $invoice->toArray(),
                $user->id
            );

            return $invoice;
        });
    }

    /**
     * Catat pembayaran / pelunasan faktur.
     */
    public function recordPayment(
        SalesInvoice $invoice,
        float $amount,
        string $method,
        ?string $refNo,
        User $user,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($invoice, $amount, $method, $refNo, $user, $notes) {
            $lockedInvoice = SalesInvoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();
            $remaining = (float)$lockedInvoice->total_amount - (float)$lockedInvoice->paid_amount;

            if ($amount <= 0) {
                throw new Exception("Nominal pembayaran harus lebih besar dari 0!");
            }

            if ($amount > $remaining) {
                $remStr = number_format($remaining, 0, ',', '.');
                throw new Exception("Nominal pembayaran melebihi sisa tagihan faktur (Maksimal: Rp {$remStr})!");
            }

            $paymentNo = $this->docNumberService->generate('PAY');

            $payment = Payment::create([
                'payment_no' => $paymentNo,
                'payment_date' => Carbon::today(),
                'sales_invoice_id' => $lockedInvoice->id,
                'customer_id' => $lockedInvoice->customer_id,
                'accounting_user_id' => $user->id,
                'amount' => $amount,
                'payment_method' => $method,
                'reference_no' => $refNo,
                'notes' => $notes,
            ]);

            $newPaid = (float)$lockedInvoice->paid_amount + $amount;
            $lockedInvoice->paid_amount = $newPaid;

            if ($newPaid >= (float)$lockedInvoice->total_amount) {
                $lockedInvoice->payment_status = 'PAID';
            } else {
                $lockedInvoice->payment_status = 'PARTIAL';
            }
            $lockedInvoice->save();

            // Kurangi piutang berjalan pelanggan
            $this->creditCheckService->decreaseCredit($lockedInvoice->customer_id, $amount);

            $this->auditTrailService->log(
                'RECORD_PAYMENT',
                'payments',
                $payment->id,
                null,
                $payment->toArray(),
                $user->id
            );

            return $payment;
        });
    }

    /**
     * Render dan simpan PDF Faktur Penjualan.
     */
    public function generatePdf(SalesInvoice $invoice): string
    {
        $invoice->load(['customer', 'salesOrder', 'deliveryOrder', 'items.item', 'accountingUser']);
        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice]);
        $pdf->setPaper('a4', 'portrait');

        $fileName = "invoices/{$invoice->invoice_no}.pdf";
        Storage::disk('public')->put($fileName, $pdf->output());

        $invoice->pdf_path = $fileName;
        $invoice->saveQuietly();

        return $fileName;
    }
}
