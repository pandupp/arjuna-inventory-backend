<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StockAdditionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with('customer')->latest()->get();
        return response()->json($invoices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi ini disesuaikan 100% dengan data yang dikirim dari frontend (Vue)
        $validator = Validator::make($request->all(), [
            'customer_name'   => 'required|string|max:255',
            'invoice_date'    => 'required|date',
            'source'          => 'required|string',
            'status'          => 'required|string',
            'dp'              => 'required|numeric|min:0',
            'items'           => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            // ✨ PERBAIKAN: Mengubah validasi dari 'integer' menjadi 'numeric' ✨
            'items.*.quantity'=> 'required|numeric|min:0.01',
            'items.*.price'   => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $invoice = DB::transaction(function () use ($request) {
                
                $totalAmount = 0;

                foreach ($request->items as $itemData) {
                    $item = Item::find($itemData['item_id']);
                    if ($item->stock < $itemData['quantity']) {
                        throw ValidationException::withMessages([
                           'items' => "Stok untuk '{$item->name}' tidak mencukupi (sisa: {$item->stock})"
                        ]);
                    }
                    $totalAmount += $itemData['price'] * $itemData['quantity'];
                }

                $customer = Customer::firstOrCreate(['name' => $request->customer_name]);

                $invoice = Invoice::create([
                    'invoice_number'    => 'INV-' . time(),
                    'customer_id'       => $customer->id,
                    'invoice_date'      => $request->invoice_date,
                    'sub_total'         => $totalAmount,
                    'dp'                => $request->dp,
                    'sisa'              => $totalAmount - $request->dp,
                    'status'            => $request->status,
                    'source'            => $request->source,
                ]);

                foreach ($request->items as $itemData) {
                    $invoice->invoiceItems()->create([
                        'item_id'  => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'price_at_transaction' => $itemData['price'],
                        'subtotal'    => $itemData['price'] * $itemData['quantity'],
                    ]);
                    Item::find($itemData['item_id'])->decrement('stock', $itemData['quantity']);
                }

                return $invoice;
            });

            return response()->json($invoice->load('customer', 'invoiceItems.item'), 201);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi Gagal', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()], 500);
        }
    }
    
    // ... (method lainnya tetap sama)

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        return $invoice->load('customer', 'invoiceItems.item');
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
