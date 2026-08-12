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
        Schema::create('oils', function (Blueprint $table) {
            $table->id();
            $table->string('oil_name', 150);
            $table->string('oil_code', 50)->unique();
            $table->enum('oil_type', ['engine', 'transmission', 'hydraulic', 'brake', 'differential']);
            $table->decimal('oil_life', 10, 2); // default km-based lifespan for this product
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oil');
    }
};
