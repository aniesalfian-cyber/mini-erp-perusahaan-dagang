<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockBalance;
use App\Models\StockLedger;
use App\Services\AuditTrailService;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected AuditTrailService $auditTrailService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $filter = $request->query('filter');

        $query = Item::with('stockBalance');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($filter === 'low_stock') {
            $query->whereHas('stockBalance', function ($q) {
                $q->whereRaw('available <= items.min_stock');
            });
        }

        $items = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();

        return view('items.index', compact('items', 'search', 'filter'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:items,code'],
            'name' => ['required', 'string', 'max:200'],
            'unit' => ['required', 'string', 'max:30'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'initial_stock' => ['nullable', 'numeric', 'min:0'],
        ]);

        $initialStock = (float)($validated['initial_stock'] ?? 0);
        unset($validated['initial_stock']);

        DB::transaction(function () use ($validated, $initialStock) {
            $item = Item::create($validated);

            StockBalance::create([
                'item_id' => $item->id,
                'on_hand' => $initialStock,
                'reserved' => 0,
                'available' => $initialStock,
            ]);

            if ($initialStock > 0) {
                StockLedger::create([
                    'item_id' => $item->id,
                    'transaction_type' => 'INITIAL',
                    'reference_type' => 'ITEM_CREATION',
                    'reference_id' => $item->id,
                    'reference_no' => 'INIT-' . $item->code,
                    'qty_in' => $initialStock,
                    'qty_out' => 0,
                    'balance_after' => $initialStock,
                    'notes' => 'Pencatatan saldo awal saat input master barang baru',
                    'user_id' => Auth::id(),
                    'created_at' => Carbon::now(),
                ]);
            }

            $this->auditTrailService->log(
                'CREATE_ITEM',
                'items',
                $item->id,
                null,
                $item->toArray(),
                Auth::id()
            );
        });

        return redirect()->route('items.index')->with('success', "Master barang {$validated['name']} ({$validated['code']}) berhasil ditambahkan ke katalog!");
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:items,code,' . $item->id],
            'name' => ['required', 'string', 'max:200'],
            'unit' => ['required', 'string', 'max:30'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldValues = $item->toArray();
        $validated['is_active'] = $request->has('is_active');
        $item->update($validated);

        $this->auditTrailService->log(
            'UPDATE_ITEM',
            'items',
            $item->id,
            $oldValues,
            $item->toArray(),
            Auth::id()
        );

        return redirect()->route('items.index')->with('success', "Informasi barang {$item->name} berhasil diperbarui.");
    }

    public function show(Item $item): View
    {
        $item->load('stockBalance');
        $ledger = StockLedger::where('item_id', $item->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('items.show', compact('item', 'ledger'));
    }

    public function adjustStock(Request $request, Item $item): RedirectResponse
    {
        $request->validate([
            'new_on_hand' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $this->inventoryService->adjustStock(
            $item->id,
            (float)$request->input('new_on_hand'),
            $request->input('reason'),
            Auth::id()
        );

        $this->auditTrailService->log(
            'ADJUST_STOCK',
            'items',
            $item->id,
            null,
            ['new_on_hand' => $request->input('new_on_hand'), 'reason' => $request->input('reason')],
            Auth::id()
        );

        return back()->with('success', "Stok fisik barang {$item->name} berhasil disesuaikan melalui stok opname.");
    }
}
