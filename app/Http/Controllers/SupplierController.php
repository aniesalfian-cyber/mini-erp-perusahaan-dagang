<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(
        protected AuditTrailService $auditTrailService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = Supplier::query();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();
        return view('suppliers.index', compact('suppliers', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:suppliers,code'],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $validated['is_active'] = true;
        $supplier = Supplier::create($validated);

        $this->auditTrailService->log(
            'CREATE_SUPPLIER',
            'suppliers',
            $supplier->id,
            null,
            $supplier->toArray(),
            Auth::id()
        );

        return redirect()->route('suppliers.index')->with('success', "Pemasok {$supplier->name} ({$supplier->code}) berhasil didaftarkan!");
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldValues = $supplier->toArray();
        $validated['is_active'] = $request->has('is_active');
        $supplier->update($validated);

        $this->auditTrailService->log(
            'UPDATE_SUPPLIER',
            'suppliers',
            $supplier->id,
            $oldValues,
            $supplier->toArray(),
            Auth::id()
        );

        return redirect()->route('suppliers.index')->with('success', "Informasi pemasok {$supplier->name} berhasil diperbarui.");
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['purchaseOrders' => function ($q) {
            $q->orderBy('created_at', 'desc')->take(10);
        }, 'goodsReceipts' => function ($q) {
            $q->orderBy('created_at', 'desc')->take(10);
        }]);

        return view('suppliers.show', compact('supplier'));
    }
}
