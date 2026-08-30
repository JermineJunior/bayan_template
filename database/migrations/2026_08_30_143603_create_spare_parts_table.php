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
        Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_number', 50)->unique();
            $table->string('name', 150);
            $table->string('category', 100)->nullable(); // e.g. "tires" now lives here as a category value, not its own table
            $table->foreignId('default_supplier_id')->nullable()->constrained('suppliers');
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('minimum_quantity', 10, 2)->default(0);
            $table->timestamps();

            // No quantity_on_hand column — always derived live from summing
            // spare_part_transactions.quantity for this part.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_parts');
    }
};
