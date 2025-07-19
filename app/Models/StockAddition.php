<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAddition extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Pastikan tidak ada spasi ekstra di sini.
     */
    protected $fillable = [
        'item_id',
        'quantity_added',
        'supplier_name',
        'notes',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model Item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}