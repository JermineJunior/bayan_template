<?php

use App\Models\Department;
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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string("full_name", 200);
            $table->string("national_id", 50)->unique();
            $table->string("phone_number", 20)->nullable();
            $table->string("department", 100)->nullable();
            $table->date("hire_date")->nullable();
            $table->enum("license_type",["general","private","other"])->nullable();
            $table->date("license_expiry_date")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");
            $table->foreignIdFor(Department::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
