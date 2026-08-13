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
        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->string('policy_number',60);
            $table->string('insurance_company',150);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('value',10,2);
            $table->boolean('is_current')->default(true);
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['vehicle_id','is_current']);
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_policies');
    }
};
