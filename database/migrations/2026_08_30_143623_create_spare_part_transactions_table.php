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
        Schema::create('spare_part_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spare_part_id')->constrained('spare_parts');
            $table->enum('type', ['purchase', 'issue', 'stocktake']);

            // Signed on purpose: always the actual change to stock, regardless of type.
            // purchase -> always positive, issue -> always negative, stocktake -> the
            // signed delta between the physical count and the system count at that
            // moment. quantity_on_hand is just SUM(quantity) across all rows for a part.
            $table->decimal('quantity', 10, 2);

            $table->foreignId('maintenance_order_id')->nullable()->constrained('maintenances'); // required when type = issue, validated in controller
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers'); // required when type = purchase
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['spare_part_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_part_transactions');
    }
};
