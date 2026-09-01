<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * قيمة التأمين يمكن أن تتجاوز الحد الأقصى القديم decimal(10,2)
     * (~9.99 مليون) — مثلاً 600 مليون في حالات الأساطيل الكبيرة،
     * لذلك يتم توسيع العمود إلى decimal(18,2).
     */
    public function up(): void
    {
        Schema::table('insurance_policies', function (Blueprint $table) {
            $table->decimal('value', 18, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurance_policies', function (Blueprint $table) {
            $table->decimal('value', 10, 2)->change();
        });
    }
};
