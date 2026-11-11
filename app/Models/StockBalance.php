<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'on_hand',
        'reserved',
        'available',
    ];

    protected function casts(): array
    {
        return [
            'on_hand' => 'decimal:2',
            'reserved' => 'decimal:2',
            'available' => 'decimal:2',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
