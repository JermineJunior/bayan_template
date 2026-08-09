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
        Schema::create('vehicle_driver', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->date('assignment_date');
            $table->boolean('is_current')->default(true);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ended_at')->nullable();

            // Generated columns enforcing a single current assignment per vehicle
            // and per driver: the column holds the related id only while the row
            // is current, so the UNIQUE index allows many NULLs (ended rows) but
            // rejects a second "current" row for the same vehicle/driver.
            $table->unsignedBigInteger('current_vehicle_id')
                ->storedAs('CASE WHEN is_current = 1 THEN vehicle_id ELSE NULL END')
                ->unique();
            $table->unsignedBigInteger('current_driver_id')
                ->storedAs('CASE WHEN is_current = 1 THEN driver_id ELSE NULL END')
                ->unique();

            $table->index(['vehicle_id', 'is_current']);
            $table->index(['driver_id', 'is_current']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_driver');
    }
};
