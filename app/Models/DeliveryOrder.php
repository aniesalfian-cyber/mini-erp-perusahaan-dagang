<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'do_no',
        'do_date',
        'sales_order_id',
        'customer_id',
        'logistics_user_id',
        'driver_name',
        'vehicle_no',
        'delivery_status',
        'recipient_name',
        'delivered_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'do_date' => 'date',
            'delivered_at' => 'datetime',
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function logisticsUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logistics_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryOrderItem::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'id', 'delivery_order_id');
    }
}
