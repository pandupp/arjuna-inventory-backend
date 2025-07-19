<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Menampilkan daftar semua customer.
     */
    public function index()
    {
        $customers = Customer::latest()->get();
        
        // Menyeragamkan format response
        return response()->json([
            'success' => true,
            'message' => 'Daftar data pelanggan berhasil dimuat.',
            'data'    => $customers
        ], 200);
    }

    /**
     * Menyimpan customer baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:25',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        // Menyeragamkan format response
        return response()->json([
            'success' => true,
            'message' => 'Pelanggan baru berhasil ditambahkan.',
            'data'    => $customer
        ], 201);
    }

    /**
     * Menampilkan detail satu customer.
     */
    public function show(Customer $customer)
    {
        // Menyeragamkan format response
        return response()->json([
            'success' => true,
            'message' => 'Detail data pelanggan berhasil dimuat.',
            'data'    => $customer
        ], 200);
    }

    /**
     * Memperbarui data customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:25',
            'address' => 'nullable|string',
        ]);

        $customer->update($validated);

        // Menyeragamkan format response
        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil diperbarui.',
            'data'    => $customer
        ], 200);
    }

    /**
     * Menghapus data customer.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        // Menyeragamkan format response
        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil dihapus.'
        ], 200); // Menggunakan 200 agar bisa mengirim pesan
    }
}