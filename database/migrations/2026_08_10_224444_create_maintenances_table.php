<?php

use App\Models\Vehicle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('maintenance_number')->unique();
            $table->foreignIdFor(Vehicle::class)->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('odometer_reading');
            $table->string('reason')->nullable();
            $table->string('workshop')->nullable();
            $table->string('technical')->nullable();
            $table->decimal('labor_cost',15,2)->nullable();
            $table->decimal('spare_cost',15,2)->nullable();
            $table->decimal('total_cost',15,2)->nullable();
            $table->enum('type',['periodic','preventive','emergency'])->default('periodic');
            $table->enum('status',['draft','pending','in_progress','completed','cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
