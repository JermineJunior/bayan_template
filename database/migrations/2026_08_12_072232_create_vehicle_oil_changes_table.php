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
        Schema::create('vehicle_oil_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('oil_id')->constrained('oils');
            $table->date('last_change');
            $table->decimal('odometer_when_change', 10, 2);
            // Stored, not derived: computed once at creation as
            // odometer_when_change + oil.oil_life (at that moment), then frozen.
            // Stays historically accurate even if the oil's oil_life is edited later.
            $table->decimal('next_change_odometer', 10, 2);
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['vehicle_id', 'oil_id', 'last_change']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_oil_changes');
    }
};
