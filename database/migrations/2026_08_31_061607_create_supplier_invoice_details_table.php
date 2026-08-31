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
        Schema::create('supplier_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_invoice_id')->constrained('supplier_invoices');
            $table->foreignId('spare_part_id')->constrained('spare_parts');
            $table->decimal('qty', 10, 2);
            $table->decimal('price', 10, 2);
            $table->timestamps();
            // No row_sub_total column — always derived (qty * price), same as invoice_details.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_invoice_details');
    }
};
