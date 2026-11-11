<?php

namespace App\Repositories;

use App\Models\SalesOrder;
use Illuminate\Pagination\LengthAwarePaginator;

class SalesOrderRepository
{
    public function getOrders(int $perPage = 15, ?string $status = null, ?string $search = null): LengthAwarePaginator
    {
        $query = SalesOrder::with(['customer', 'salesUser', 'items.item']);

        if ($status && $status !== 'ALL') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getPendingBackorders(): \Illuminate\Database\Eloquent\Collection
    {
        return SalesOrder::with(['customer', 'items.item'])
            ->whereIn('status', ['CONFIRMED', 'PROCESSING', 'PARTIAL_SHIPPED'])
            ->whereHas('items', function ($q) {
                $q->whereRaw('qty_ordered > qty_shipped');
            })
            ->orderBy('order_date', 'asc')
            ->get();
    }
}
