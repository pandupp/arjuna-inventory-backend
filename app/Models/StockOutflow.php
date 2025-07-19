<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOutflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'quantity_out',
        'reason',
        'notes',
    ];

    /**
     * Mendefinisikan relasi ke model Item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}