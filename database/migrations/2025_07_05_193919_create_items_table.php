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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique(); // Kode harus unik
            $table->string('name');
            $table->string('type');
            $table->string('quality');
            $table->string('unit')->default('Pcs');
            $table->string('supplier_name');
            $table->integer('stock')->default(0);
            
            // ✨ INI ADALAH KOLOM KUNCI YANG HILANG/SALAH
            $table->decimal('unit_price', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
