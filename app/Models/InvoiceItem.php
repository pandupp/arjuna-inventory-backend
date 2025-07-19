<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Atribut yang diizinkan untuk diisi secara massal.
     * Daftar ini HARUS cocok dengan kolom di database dan controller.
     */
    protected $fillable = [
    'invoice_id',
    'item_id',
    'quantity',
    'price_at_transaction', // Sesuaikan dengan nama kolom di migrasi
    'subtotal',             // Sesuaikan dengan nama kolom di migrasi
];

    /**
     * Mendefinisikan relasi "belongsTo" ke model Item.
     * Setiap InvoiceItem merujuk ke satu Item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model Invoice.
     * Setiap InvoiceItem dimiliki oleh satu Invoice.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
