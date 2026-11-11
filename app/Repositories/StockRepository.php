<?php

namespace App\Repositories;

use App\Models\StockBalance;
use App\Models\StockLedger;
use Illuminate\Pagination\LengthAwarePaginator;

class StockRepository
{
    public function getStockOverview(int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        $query = StockBalance::with('item');

        if ($search) {
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function getLedgerByItem(int $itemId, int $perPage = 25): LengthAwarePaginator
    {
        return StockLedger::where('item_id', $itemId)
            ->with(['user', 'item'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
