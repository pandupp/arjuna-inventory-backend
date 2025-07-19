<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    /**
     * Atribut yang diizinkan untuk diisi secara massal.
     */
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'user_id', // ✅ 1. Tambahkan user_id di sini
        'invoice_date',
        'sub_total',
        'dp',
        'sisa',
        'status',
        'source',
        'note',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model Customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Mendefinisikan relasi "hasMany" ke model InvoiceItem.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * ✅ 2. Tambahkan relasi "belongsTo" ke model User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
