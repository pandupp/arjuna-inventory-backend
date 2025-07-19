<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // ✅ 2. Ambil juga data user saat memuat riwayat
        $invoices = Invoice::with(['customer', 'user'])->latest()->get();
        return response()->json($invoices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'invoice_date'  => 'required|date',
            'source'        => 'required|string',
            'dp'            => 'nullable|numeric|min:0',
            'items'         => 'required|array|min:1',
            'items.*.item_id'  => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price'    => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Mulai transaksi database untuk memastikan semua operasi berhasil
            $invoice = DB::transaction(function () use ($request) {
                
                // 1. Cari atau buat customer baru
                $customer = Customer::firstOrCreate(['name' => $request->customer_name]);

                // 2. Buat invoice utama terlebih dahulu dengan total sementara
                $invoice = Invoice::create([
                    'invoice_number' => 'INV-' . time(),
                    'customer_id'    => $customer->id,
                    'user_id'        => Auth::id(), // ✅ 3. Simpan ID user yang sedang login
                    'invoice_date'   => $request->invoice_date,
                    'source'         => $request->source,
                    'dp'             => $request->dp ?? 0,
                    'sub_total'      => 0, // Total sementara
                    'sisa'           => 0, // Sisa sementara
                    'status'         => 'Belum Lunas', // Status awal
                ]);

                $grandTotal = 0;

                // 3. Loop melalui setiap item di keranjang
                foreach ($request->items as $itemData) {
                    $item = Item::find($itemData['item_id']);

                    // Cek ketersediaan stok
                    if ($item->stock < $itemData['quantity']) {
                        throw new \Exception("Stok untuk item '{$item->name}' tidak mencukupi.");
                    }

                    $subtotal = $itemData['quantity'] * $itemData['price'];
                    $grandTotal += $subtotal;

                    // 4. Simpan setiap item ke tabel relasi (invoice_items)
                    $invoice->invoiceItems()->create([
                        'item_id'  => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'price_at_transaction' => $itemData['price'],
                        'subtotal' => $subtotal,
                    ]);

                    // 5. Kurangi stok dari tabel items
                    $item->decrement('stock', $itemData['quantity']);
                }

                // 6. Update invoice dengan total dan status akhir yang benar
                $dp = (float)($request->dp ?? 0);
                $sisa = $grandTotal - $dp;

                $invoice->sub_total = $grandTotal;
                $invoice->sisa = $sisa;
                $invoice->status = ($sisa <= 0) ? 'Lunas' : 'Belum Lunas';
                $invoice->save();

                return $invoice;
            });

            return response()->json($invoice->load(['customer', 'user', 'invoiceItems.item']), 201);

        } catch (\Exception $e) {
            // Jika terjadi error (misal: stok habis), kembalikan pesan error
            return response()->json([
                'message' => 'Terjadi kesalahan pada server', 
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        // ✅ 4. Ambil juga data user untuk halaman print
        return $invoice->load(['customer', 'user', 'invoiceItems.item']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => 'sometimes|required|string',
            'note' => 'nullable|string',
        ]);

        $invoice->update($validated);
        return response()->json($invoice->load('customer', 'invoiceItems.item'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        DB::transaction(function () use ($invoice) {
            foreach ($invoice->invoiceItems as $invoiceItem) {
                Item::find($invoiceItem->item_id)->increment('stock', $invoiceItem->quantity);
            }
            $invoice->delete();
        });

        return response()->json(null, 204);
    }

    /**
     * Menandai invoice sebagai lunas.
     */
    public function markAsPaid(Invoice $invoice)
    {
        if ($invoice->status === 'Lunas') {
            return response()->json(['message' => 'Invoice ini sudah lunas.'], 400);
        }

        $invoice->status = 'Lunas';
        $invoice->dp = $invoice->sub_total;
        $invoice->sisa = 0;
        $invoice->save();

        return response()->json($invoice, 200);
    }
}
