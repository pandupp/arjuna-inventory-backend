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
        // Mengubah kolom 'stock' di tabel 'items'
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('stock', 15, 2)->change();
        });

        // Mengubah kolom 'quantity' di tabel 'invoice_items'
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('quantity', 15, 2)->change();
        });

        // Mengubah kolom 'quantity_added' di tabel 'stock_additions'
        Schema::table('stock_additions', function (Blueprint $table) {
            $table->decimal('quantity_added', 15, 2)->change();
        });

        // Mengubah kolom 'quantity_out' di tabel 'stock_outflows'
        Schema::table('stock_outflows', function (Blueprint $table) {
            $table->decimal('quantity_out', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Mengembalikan ke integer jika di-rollback
        Schema::table('items', function (Blueprint $table) {
            $table->integer('stock')->change();
        });
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });
        Schema::table('stock_additions', function (Blueprint $table) {
            $table->integer('quantity_added')->change();
        });
        Schema::table('stock_outflows', function (Blueprint $table) {
            $table->integer('quantity_out')->change();
        });
    }
};
