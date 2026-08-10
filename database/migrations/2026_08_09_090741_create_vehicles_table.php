<?php

use App\Models\Management;
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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('internal_number', 50)->unique();
            $table->string('plate_number', 50)->unique();
            $table->string('type', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->year('manufacture_year')->nullable();
            $table->string('color', 100)->nullable();
            $table->string('chassis_number', 100)->nullable();
            $table->string('engine_number', 100)->nullable();
            $table->enum('fuel_type', ['gasoline', 'diesel'])->nullable();
            $table->string('engine_capacity', 100)->nullable();
            $table->foreignIdFor(Management::class)->nullable()->constrained()->cascadeOnDelete();
            $table->enum('status', ['active',
                'maintenance',
                'stopped',
                'sold',
                'out_of_service', ])->default('active');
            $table->decimal('current_odometer', 10, 2)->nullable(); // العداد الحالي
            $table->decimal('operating_hours', 10, 2)->nullable(); // ساعات التشغيل
            $table->string('image_path', 255)->nullable(); // مسار الصورة
            $table->softDeletes(); // إضافة عمود deleted_at لدعم الحذف الناعم
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
