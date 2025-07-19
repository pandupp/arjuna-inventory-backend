<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- 1. Import trait

class Item extends Model
{
    use HasFactory, SoftDeletes; // <-- 2. Gunakan trait

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_code',
        'name',
        'type',
        'quality',
        'unit',
        'supplier_name',
        'stock',
        'unit_price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    /**
     * ✨ PENYESUAIAN: Relasi ke InvoiceItems ditambahkan.
     * Mendefinisikan bahwa satu Item bisa memiliki banyak InvoiceItem.
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
