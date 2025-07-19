<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer; // Pastikan model Customer di-import

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat pelanggan default dengan ID 1
        // Ini akan menjadi pelanggan "umum" atau "walk-in"
        Customer::create([
            'id' => 1,
            'name' => 'Walk-in Customer',
            'phone' => '000',
            'address' => '-'
        ]);

        // Anda bisa menambahkan pelanggan lain di sini jika perlu
        // Customer::create([
        //     'name' => 'PT Jaya Abadi',
        //     'phone' => '08123456789',
        //     'address' => 'Jalan Sudirman No. 1'
        // ]);
    }
}
