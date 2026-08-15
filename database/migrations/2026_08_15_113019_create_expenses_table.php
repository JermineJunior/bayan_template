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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->enum('expense_type', [
                'fuel',
                'oil',
                'filter',
                'maintenance',
                'tires',
                'spare_parts',
                'insurance',
                'license',
                'violations',
                'other',
            ]);
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->string('description')->nullable();

            // Set only on auto-generated rows (fuel/oil/filter for now) — points back to the
            // FuelLog / VehicleOilChange / VehicleFilterChange that created this expense.
            // Null on manually-entered expenses.
            $table->nullableMorphs('sourceable');

            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['vehicle_id', 'expense_date']);
            $table->index('expense_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
