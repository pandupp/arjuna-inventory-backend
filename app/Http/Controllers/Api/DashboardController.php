<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Mengumpulkan semua data yang dibutuhkan untuk dashboard.
     */
    public function index()
    {
        // 1. Top 3 produk terlaris
        $topSellingItems = DB::table('invoice_items')
            ->join('items', 'invoice_items.item_id', '=', 'items.id')
            ->select('items.name', 'items.item_code', DB::raw('SUM(invoice_items.quantity) as total_sold'))
            ->groupBy('items.id', 'items.name', 'items.item_code')
            ->orderBy('total_sold', 'desc')
            ->limit(3)
            ->get();

        // 2. Stok item saat ini (✅ DITAMBAHKAN item_code)
        $currentStock = Item::select('item_code', 'name', 'stock')
            ->orderBy('stock', 'desc')
            ->limit(10)
            ->get();

        // 3. Traffic berdasarkan sumber
        $trafficBySource = Invoice::select('source', DB::raw('COUNT(*) as transaction_count'))
            ->groupBy('source')
            ->get();
            
        // 4. History penggunaan stok spanduk/baliho (✅ DIUBAH MENJADI PER BULAN)
        $bannerUsageHistory = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('items', 'invoice_items.item_id', '=', 'items.id')
            ->select(
                DB::raw("DATE_FORMAT(invoices.invoice_date, '%Y-%m') as month"), // Dikelompokkan per bulan
                DB::raw('SUM(invoice_items.quantity) as total_usage')
            )
            ->where(function ($query) {
                $query->where('items.name', 'LIKE', '%Spanduk%')
                      ->orWhere('items.name', 'LIKE', '%Baliho%');
            })
            ->where('invoices.invoice_date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Gabungkan semua data ke dalam satu response
        return response()->json([
            'top_selling_items' => $topSellingItems,
            'current_stock' => $currentStock,
            'traffic_by_source' => $trafficBySource,
            'banner_usage_history' => $bannerUsageHistory,
        ]);
    }
}
