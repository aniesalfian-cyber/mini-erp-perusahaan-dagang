<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected AuditTrailService $auditTrailService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = Customer::query();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();
        return view('customers.index', compact('customers', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:customers,code'],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $validated['current_credit'] = 0;
        $validated['is_active'] = true;

        $customer = Customer::create($validated);

        $this->auditTrailService->log(
            'CREATE_CUSTOMER',
            'customers',
            $customer->id,
            null,
            $customer->toArray(),
            Auth::id()
        );

        return redirect()->route('customers.index')->with('success', "Pelanggan {$customer->name} ({$customer->code}) berhasil didaftarkan!");
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldValues = $customer->toArray();
        $validated['is_active'] = $request->has('is_active');
        $customer->update($validated);

        $this->auditTrailService->log(
            'UPDATE_CUSTOMER',
            'customers',
            $customer->id,
            $oldValues,
            $customer->toArray(),
            Auth::id()
        );

        return redirect()->route('customers.index')->with('success', "Informasi pelanggan {$customer->name} berhasil diperbarui.");
    }

    public function show(Customer $customer): View
    {
        $customer->load(['salesOrders' => function ($q) {
            $q->orderBy('created_at', 'desc')->take(10);
        }, 'invoices' => function ($q) {
            $q->orderBy('created_at', 'desc')->take(10);
        }]);

        return view('customers.show', compact('customer'));
    }
}
