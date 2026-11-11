<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'order_date',
        'customer_id',
        'sales_user_id',
        'total_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    public function getTotalOrderedQtyAttribute(): float
    {
        return (float)$this->items->sum('qty_ordered');
    }

    public function getTotalShippedQtyAttribute(): float
    {
        return (float)$this->items->sum('qty_shipped');
    }

    public function getPendingBackorderQtyAttribute(): float
    {
        return max(0, $this->total_ordered_qty - $this->total_shipped_qty);
    }
}
