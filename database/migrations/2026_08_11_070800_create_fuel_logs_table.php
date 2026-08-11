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
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('driver_id')->nullable()->constrained('drivers');
            $table->dateTime('filled_at');
            $table->enum('fuel_type', ['gasoline', 'diesel'])->nullable();
            $table->decimal('liters', 8, 2);
            $table->decimal('price_per_liter', 8, 3);
            $table->decimal('discount', 10, 2)->nullable();
            // عمود محسوب — القيمة الإجمالية = لترات × سعر اللتر − الخصم (إن وُجد)، ما يُدخل يدويًا
            $table->decimal('total_value', 10, 2)->storedAs('liters * price_per_liter - COALESCE(discount, 0)');
            $table->decimal('odometer_reading', 10, 2);
            $table->string('station')->nullable();
            $table->string('invoice_number')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['vehicle_id', 'filled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_logs');
    }
};
