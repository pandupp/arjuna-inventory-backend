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
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
        $table->string('invoice_number')->unique();
        $table->date('invoice_date'); // <-- PASTIKAN NAMANYA INI
        $table->decimal('sub_total', 15, 2)->default(0.00);
        $table->decimal('dp', 15, 2)->default(0.00);
        $table->decimal('sisa', 15, 2)->default(0.00);
        $table->text('note')->nullable();
        $table->string('status');
        $table->string('source')->default('Customer Order');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
