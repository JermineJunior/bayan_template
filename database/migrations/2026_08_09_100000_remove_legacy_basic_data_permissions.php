<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Remove the legacy, module-wide basic-data.* permissions.
     *
     * They were replaced by per-module permissions (managements.*,
     * departments.*, drivers.*, vehicles.*). Deleting a permission also
     * cascades to the role/user pivot tables.
     */
    public function up(): void
    {
        Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'like', 'basic-data.%')
            ->delete();
    }

    /**
     * Re-create the legacy permissions if this migration is ever rolled back.
     */
    public function down(): void
    {
        foreach (['view', 'create', 'edit', 'delete'] as $action) {
            DB::table('permissions')->insertOrIgnore([
                'name' => "basic-data.{$action}",
                'guard_name' => 'web',
            ]);
        }
    }
};
