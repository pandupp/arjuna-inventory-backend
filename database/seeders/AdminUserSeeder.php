<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!User::where('email', 'pandu@arjuna.com')->exists()) {
            User::create([
                'name' => 'Admin Arjuna',
                'email' => 'pandu@arjuna.com',
                'password' => Hash::make('password_final_aman'), // Ganti passwordmu
                'role' => 'admin', // <-- TAMBAHKAN ROLE DI SINI
            ]);
        }
    }
}