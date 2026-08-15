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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('report_number', 50)->unique();
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('driver_id')->nullable()->constrained('drivers');
            $table->date('incident_date');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->decimal('repair_cost', 10, 2)->nullable();
            $table->foreignId('insurance_policy_id')->nullable()->constrained('insurance_policies');
            $table->enum('claim_status', ['pending', 'approved', 'rejected', 'paid'])->nullable();

            
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['vehicle_id', 'incident_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
