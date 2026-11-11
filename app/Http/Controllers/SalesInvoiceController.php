<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\Payment;
use App\Models\SalesInvoice;
use App\Services\AccountingService;
use App\Services\AuditTrailService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SalesInvoiceController extends Controller
{
    public function __construct(
        protected AccountingService $accountingService,
        protected AuditTrailService $auditTrailService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = SalesInvoice::with(['customer', 'salesOrder', 'deliveryOrder', 'accountingUser']);

        if ($status && $status !== 'ALL') {
            $query->where('payment_status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Surat Jalan yang siap difakturkan (status DELIVERED atau IN_TRANSIT dan belum memiliki invoice)
        $unbilledDOs = DeliveryOrder::with(['customer', 'salesOrder', 'items.item'])
            ->whereDoesntHave('invoice')
            ->whereIn('delivery_status', ['DELIVERED', 'IN_TRANSIT'])
            ->orderBy('do_date', 'asc')
            ->get();

        return view('invoices.index', compact('invoices', 'unbilledDOs', 'status', 'search'));
    }

    public function createFromDO(Request $request): RedirectResponse
    {
        $request->validate([
            'delivery_order_id' => ['required', 'exists:delivery_orders,id'],
            'due_days' => ['nullable', 'integer', 'min:1', 'max:180'],
        ]);

        try {
            $do = DeliveryOrder::with(['customer', 'salesOrder.items', 'items.item'])
                ->findOrFail($request->input('delivery_order_id'));

            if ($do->invoice) {
                return back()->with('error', "Surat Jalan {$do->do_no} sudah diterbitkan faktur sebelumnya ({$do->invoice->invoice_no}).");
            }

            $dueDays = (int)($request->input('due_days') ?? 30);
            $invoice = $this->accountingService->createInvoiceFromDO($do, Auth::user(), $dueDays);

            return redirect()->route('invoices.show', $invoice->id)
                ->with('success', "Faktur Penjualan {$invoice->invoice_no} berhasil diterbitkan dan piutang pelanggan telah dicatat!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(SalesInvoice $invoice): View
    {
        $invoice->load(['customer', 'salesOrder', 'deliveryOrder', 'items.item', 'payments.accountingUser']);
        return view('invoices.show', compact('invoice'));
    }

    public function pdf(SalesInvoice $invoice): Response
    {
        $invoice->load(['customer', 'salesOrder', 'deliveryOrder', 'items.item', 'accountingUser']);
        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream("Faktur_{$invoice->invoice_no}.pdf");
    }

    public function recordPayment(Request $request, SalesInvoice $invoice): RedirectResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:CASH,BANK_TRANSFER,GIRO'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $payment = $this->accountingService->recordPayment(
                $invoice,
                (float)$request->input('amount'),
                $request->input('payment_method'),
                $request->input('reference_no'),
                Auth::user(),
                $request->input('notes')
            );

            return back()->with('success', "Pembayaran sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " berhasil dicatat ({$payment->payment_no})!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
