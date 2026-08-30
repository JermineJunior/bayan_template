<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permission catalogue
    |--------------------------------------------------------------------------
    |
    | Single source of truth for every permission in the application, grouped
    | by feature area for readability. Each key follows the
    | `<feature>.<action>` convention and is stored verbatim in the
    | `permissions` table (guard: web).
    |
    | The PermissionSeeder reads this file and syncs it into the database:
    | permissions listed here are created if missing, and any permission that
    | exists in the DB but no longer appears here is reported.
    |
    | To add a permission: add its key to the relevant group, then re-run
    |
    |   php artisan db:seed --class=PermissionSeeder
    |   php artisan db:seed --class=RoleSeeder        # re-syncs seeded roles
    |
    */

    'users' => [
        'users.view',
        'users.create',
        'users.edit',
        'users.delete',
    ],

    'roles' => [
        'roles.view',
        'roles.create',
        'roles.edit',
        'roles.delete',
    ],

    'settings' => [
        'settings.edit',
    ],

    'managements' => [
        'managements.view',
        'managements.create',
        'managements.edit',
        'managements.delete',
    ],

    'departments' => [
        'departments.view',
        'departments.create',
        'departments.edit',
        'departments.delete',
    ],

    'vehicles' => [
        'vehicles.view',
        'vehicles.create',
        'vehicles.edit',
        'vehicles.delete',
        'vehicles.assign',
        'vehicles.end-assignment',
    ],

    'drivers' => [
        'drivers.view',
        'drivers.create',
        'drivers.edit',
        'drivers.delete',
    ],

    'odometer' => [
        'odometer.update',
        'odometer.correct',
    ],

    'fuel' => [
        'fuel.view',
        'fuel.create',
        'fuel.delete',
    ],

    'oils' => [
        'oils.view',
        'oils.create',
        'oils.edit',
        'oils.delete',
    ],

    'oil-changes' => [
        'oil-changes.view',
        'oil-changes.create',
    ],

    'filters' => [
        'filters.view',
        'filters.create',
        'filters.edit',
        'filters.delete',
    ],

    'filter-changes' => [
        'filter-changes.view',
        'filter-changes.create',
    ],

    'insurance-policies' => [
        'insurance-policies.view',
        'insurance-policies.create',
    ],

    'violations' => [
        'violations.view',
        'violations.create',
        'violations.edit',
        'violations.delete',
    ],

    'incidents' => [
        'incidents.view',
        'incidents.create',
        'incidents.edit',
        'incidents.delete',
    ],
    'maintenance' => [
        'maintenance.view',
        'maintenance.create',
        'maintenance.edit',
        'maintenance.delete',
    ],

    'expenses' => [
        'expenses.view',
        'expenses.create',
        'expenses.delete',
    ],

    'suppliers' => [
        'suppliers.view',
        'suppliers.create',
        'suppliers.edit',
        'suppliers.delete',
    ],

    'spare-parts' => [
        'spare-parts.view',
        'spare-parts.create',
        'spare-parts.edit',
        'spare-parts.delete',
    ],

];
