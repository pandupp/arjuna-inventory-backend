<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockOutflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockOutflowController extends Controller
{
    /**
     * Menyimpan data stok keluar baru dan mengurangi stok master.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'quantity_out' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $item = Item::find($request->item_id);

        // Validasi tambahan: pastikan stok mencukupi
        if ($item->stock < $request->quantity_out) {
            return response()->json(['errors' => ['quantity_out' => ['Stok tidak mencukupi. Sisa stok: ' . $item->stock]]], 422);
        }

        $stockOutflow = DB::transaction(function () use ($request, $item) {
            // 1. Kurangi stok di tabel master (items)
            $item->decrement('stock', $request->quantity_out);

            // 2. Catat riwayat di tabel stok keluar (stock_outflows)
            $outflow = StockOutflow::create([
                'item_id' => $request->item_id,
                'quantity_out' => $request->quantity_out,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            return $outflow;
        });

        return response()->json($stockOutflow->load('item'), 201);
    }


    /**
     * Menghapus data stok keluar dan mengembalikan stok master.
     */
    public function destroy(StockOutflow $stockOutflow)
    {
        DB::transaction(function () use ($stockOutflow) {
            // 1. Kembalikan stok ke tabel master (items)
            Item::find($stockOutflow->item_id)->increment('stock', $stockOutflow->quantity_out);

            // 2. Hapus catatan dari tabel stok keluar
            $stockOutflow->delete();
        });

        return response()->json(null, 204);
    }
}