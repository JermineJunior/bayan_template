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
        Schema::create('vehicle_filter_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('filter_id')->constrained('filters');
            $table->date('last_change');
            $table->decimal('odometer_when_change', 10, 2);
            // Stored, not derived: computed once at creation as
            // odometer_when_change + filter.filter_life, then frozen — same rationale as
            // vehicle_oil_changes.next_change_odometer.
            $table->decimal('next_change_odometer', 10, 2);
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['vehicle_id', 'filter_id', 'last_change']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_filter_changes');
    }
};
