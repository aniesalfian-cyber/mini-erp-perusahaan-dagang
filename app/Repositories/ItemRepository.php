<?php

namespace App\Repositories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ItemRepository
{
    public function getAll(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = Item::with('stockBalance');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name', 'asc')->paginate($perPage);
    }

    public function findById(int $id): ?Item
    {
        return Item::with(['stockBalance', 'stockLedgers'])->find($id);
    }

    public function findByCode(string $code): ?Item
    {
        return Item::where('code', $code)->first();
    }

    public function create(array $data): Item
    {
        return Item::create($data);
    }

    public function update(Item $item, array $data): bool
    {
        return $item->update($data);
    }

    public function delete(Item $item): bool
    {
        return $item->delete();
    }
}
