<?php

use App\Models\SparePart;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The invoice_details migration originally ran before spare_parts existed,
     * so its spare_part_id foreign key was never created. This migration adds
     * the missing column now that the spare_parts table is available.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('invoice_details', 'spare_part_id')) {
            Schema::table('invoice_details', function (Blueprint $table) {
                $table->foreignIdFor(SparePart::class)->nullable()->after('invoice_id')->constrained();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('spare_part_id');
        });
    }
};
