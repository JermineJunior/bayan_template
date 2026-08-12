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
        Schema::create('filters', function (Blueprint $table) {
            $table->id();
            $table->string('filter_name', 150);
            $table->string('filter_code', 50)->unique();
            $table->enum('filter_type', ['oil', 'air', 'fuel', 'ac']);
            $table->decimal('filter_life', 10, 2); // default km-based lifespan for this product
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filters');
    }
};
