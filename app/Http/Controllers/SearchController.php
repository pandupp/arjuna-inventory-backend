<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Invoice;
use App\Models\User;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->input('q');

        if (!$keyword) {
            return response()->json([
                'inventory' => [],
                'invoices'  => [],
                'users'     => [],
            ]);
        }

        // 1. Cari di tabel Inventory (items) - Sudah Benar
        $inventoryResults = Item::where(function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('item_code', 'LIKE', "%{$keyword}%");
            })
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'text' => "{$item->item_code} - {$item->name}",
                    'url'  => "/report/detail/{$item->id}"
                ];
            });

        // 2. Cari di tabel Invoices
        // ▼▼▼ PERBAIKAN UTAMA ADA DI SINI ▼▼▼
        $invoiceResults = Invoice::with('customer') // Eager load relasi customer
            ->where(function ($query) use ($keyword) {
                // Cari berdasarkan kolom 'number' di tabel invoices
                $query->where('number', 'LIKE', "%{$keyword}%")
                      // Cari berdasarkan kolom 'name' di tabel customers melalui relasi
                      ->orWhereHas('customer', function ($subQuery) use ($keyword) {
                          $subQuery->where('name', 'LIKE', "%{$keyword}%");
                      });
            })
            ->limit(5)
            ->get()
            ->map(function ($invoice) {
                // Pastikan ada customer untuk menghindari error
                $customerName = $invoice->customer ? $invoice->customer->name : 'N/A';
                return [
                    'id'   => $invoice->id,
                    // Gunakan 'number' dan nama customer dari relasi
                    'text' => "{$invoice->number} (Pelanggan: {$customerName})",
                    'url'  => "/invoice/print/{$invoice->id}"
                ];
            });

        // 3. Cari di tabel Users - Sudah Benar
        $userResults = User::where(function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('email', 'LIKE', "%{$keyword}%");
            })
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id'   => $user->id,
                    'text' => "{$user->name} ({$user->email})",
                    'url'  => "/user?edit={$user->id}"
                ];
            });


        // Gabungkan semua hasil dan kembalikan sebagai JSON
        return response()->json([
            'inventory' => $inventoryResults,
            'invoices'  => $invoiceResults,
            'users'     => $userResults,
        ]);
    }
}
