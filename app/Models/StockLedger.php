<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLedger extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'stock_ledger';

    protected $fillable = [
        'item_id',
        'transaction_type',
        'reference_type',
        'reference_id',
        'reference_no',
        'qty_in',
        'qty_out',
        'balance_after',
        'notes',
        'user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'qty_in' => 'decimal:2',
            'qty_out' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
