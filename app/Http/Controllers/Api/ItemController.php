<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::latest()->get();
        $itemsWithStatus = $items->map(function ($item) {
            if ($item->stock > 100) {
                $item->status = 'High';
            } elseif ($item->stock > 20) {
                $item->status = 'Medium';
            } else {
                $item->status = 'Low';
            }
            return $item;
        });
        return response()->json([
            'success' => true,
            'message' => 'Daftar data barang berhasil dimuat.',
            'data'    => $itemsWithStatus
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'type'          => 'required|string|max:255',
            'quality'       => 'required|string|max:255',
            'unit'          => 'nullable|string|max:50',
            'supplier_name' => 'required|string|max:255',
            'stock'         => 'required|numeric|min:0',
            'unit_price'    => 'nullable|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();
        $additionalData = [
            'item_code' => 'ITM-' . str_pad((DB::table('items')->max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT),
            'unit' => $request->unit ?? 'pcs',
            'unit_price' => $request->unit_price ?? 0,
        ];
        $finalData = array_merge($validatedData, $additionalData);
        Log::info('Data yang akan dibuat:', $finalData);
        $item = Item::create($finalData);
        return response()->json([
            'success' => true,
            'message' => 'Barang baru berhasil ditambahkan.',
            'data'    => $item
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail data barang berhasil dimuat.',
            'data'    => $item
        ], 200);
    }
    
    /**
     * Mengambil data gabungan untuk Kartu Stok satu item.
     */
    public function getStockCard(Item $item)
    {
        // Ambil semua riwayat stok masuk
        $stockAdditions = DB::table('stock_additions')
            ->where('item_id', $item->id)
            ->select(
                'created_at as date',
                DB::raw("CONCAT('Stok Masuk - ', supplier_name) as description"),
                'quantity_added as quantity_in',
                DB::raw('NULL as quantity_out')
            );

        // Ambil semua riwayat stok keluar (manual)
        $stockOutflows = DB::table('stock_outflows')
            ->where('item_id', $item->id)
            ->select(
                'created_at as date',
                'reason as description',
                DB::raw('NULL as quantity_in'),
                'quantity_out'
            );

        // Ambil semua riwayat penjualan dari invoice
        $sales = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoice_items.item_id', $item->id)
            ->select(
                'invoices.created_at as date',
                DB::raw("CONCAT('Penjualan - ', invoices.invoice_number) as description"),
                DB::raw('NULL as quantity_in'),
                'invoice_items.quantity as quantity_out'
            );

        // Gabungkan KETIGA riwayat, urutkan berdasarkan tanggal
        $transactions = $stockAdditions
            ->unionAll($stockOutflows)
            ->unionAll($sales)
            ->orderBy('date', 'asc')
            ->get();

        // LOGIKA BARU: MENGHITUNG MUNDUR DARI STOK AKHIR
        $currentStock = (float) $item->stock;

        $stockCard = $transactions->reverse()->map(function ($transaction) use (&$currentStock) {
            $qtyIn = (float) $transaction->quantity_in;
            $qtyOut = (float) $transaction->quantity_out;

            $transaction->balance = $currentStock;
            
            $currentStock += $qtyOut;
            $currentStock -= $qtyIn;

            return $transaction;
        });

        $finalStockCard = $stockCard->reverse();

        return response()->json([
            'item' => $item,
            'stock_card' => $finalStockCard,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'type'          => 'required|string|max:255',
            'quality'       => 'required|string|max:255',
            'unit'          => 'nullable|string|max:50',
            'supplier_name' => 'required|string|max:255',
            'stock'         => 'required|numeric|min:0',
            'unit_price'    => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($request, $item, $validator) {
            $validatedData = $validator->validated();
            
            $oldStock = (float) $item->stock;
            $newStock = (float) $validatedData['stock'];
            
            $item->update($validatedData);

            $stockChange = $newStock - $oldStock;

            if ($stockChange > 0) {
                DB::table('stock_additions')->insert([
                    'item_id' => $item->id,
                    'quantity_added' => $stockChange,
                    'supplier_name' => 'Penyesuaian Stok (Edit)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($stockChange < 0) {
                DB::table('stock_outflows')->insert([
                    'item_id' => $item->id,
                    'quantity_out' => abs($stockChange),
                    'reason' => 'Penyesuaian Stok (Edit)',
                    'notes' => 'Stok disesuaikan melalui form edit item.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Data barang berhasil diperbarui.',
            'data'    => $item->fresh()
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();
        return response()->json([ 
            'success' => true,
            'message' => 'Data barang berhasil dihapus.',
        ], 200);
    }
}