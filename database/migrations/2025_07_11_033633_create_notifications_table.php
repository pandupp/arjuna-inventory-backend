<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Menggunakan UUID agar unik dan tidak bisa ditebak
            $table->string('type'); // Tipe notifikasi, cth: 'LOW_STOCK', 'INVOICE_PAID'
            
            // Untuk siapa notifikasi ini?
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Data tambahan dalam format JSON
            // Cth: {"item_id": 1, "item_name": "Spanduk Flexi"}
            $table->json('data');
            
            // Kapan notifikasi ini dibaca?
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
