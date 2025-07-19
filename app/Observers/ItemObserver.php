<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\Notification; // <-- Jangan lupa impor model Notifikasi
use App\Models\User;         // <-- Impor model User

class ItemObserver
{
    /**
     * Handle the Item "updated" event.
     */
    public function updated(Item $item): void
    {
        // Definisikan batas stok rendah Anda di sini
        $lowStockThreshold = 20;

        // Cek jika kolom 'stock' adalah salah satu yang diubah,
        // DAN jika stok saat ini berada di bawah atau sama dengan batas
        if ($item->isDirty('stock') && $item->stock <= $lowStockThreshold) {
            
            // Tentukan siapa yang akan menerima notifikasi.
            // Untuk sekarang, kita kirim ke semua user dengan role 'manager'
            // Anda bisa sesuaikan logikanya nanti.
            $managers = User::where('role', 'manager')->get();

            foreach ($managers as $manager) {
                // Buat notifikasi untuk setiap manajer
                Notification::create([
                    'user_id' => $manager->id,
                    'type'    => 'LOW_STOCK',
                    'data'    => [
                        'item_id'   => $item->id,
                        'item_name' => $item->name,
                        'stock'     => $item->stock,
                        'unit'      => $item->unit,
                        'url'       => "/report/detail/{$item->id}" // URL untuk di-klik
                    ]
                ]);
            }
        }
    }

    /**
     * Handle the Item "created" event.
     */
    public function created(Item $item): void
    {
        //
    }

    /**
     * Handle the Item "deleted" event.
     */
    public function deleted(Item $item): void
    {
        //
    }

    /**
     * Handle the Item "restored" event.
     */
    public function restored(Item $item): void
    {
        //
    }

    /**
     * Handle the Item "force deleted" event.
     */
    public function forceDeleted(Item $item): void
    {
        //
    }
}
