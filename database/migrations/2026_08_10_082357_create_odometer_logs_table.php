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
        Schema::create('odometer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->decimal('reading', 10, 2); // قراءة العداد بالكيلومتر (أو الميل) مع خانتين عشريتين
            $table->dateTime('recorded_at');
            $table->foreignId('recorded_by')->constrained('users');
            $table->boolean('is_correction')->default(false);
            $table->string('note')->nullable(); // إلزامي منطقيًا عند is_correction = true (يُفرض بالـ Service/Validation، مو بقيد DB)
            $table->timestamps();

            $table->index(['vehicle_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odometer_logs');
    }
};
