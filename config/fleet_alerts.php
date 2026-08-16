<?php

// config/fleet_alerts.php
//
// Central definition of every automated alert type: what threshold triggers it, and
// which Spatie permission a user needs to receive it. The 'permission' values below are
// PLACEHOLDERS — replace each with the actual permission name already used in this
// project's config-driven permissions array for that module. Do not guess new ones;
// check the real permission config before filling these in.

return [
    'oil_due' => [
        'label' => 'Oil change due soon',
        // Alert fires when remaining_change <= this many km (0 or negative = already overdue)
        'threshold_km' => 500,
        'permission' => 'oil.view', // TODO: confirm actual permission name
    ],

    'filter_due' => [
        'label' => 'Filter change due soon',
        'threshold_km' => 500,
        'permission' => 'filters.view', // TODO: confirm actual permission name
    ],

    'insurance_expiring' => [
        'label' => 'Insurance policy expiring soon',
        'threshold_days' => 30,
        'permission' => 'insurance-policies.view', // TODO: confirm actual permission name
    ],

    'driver_license_expiring' => [
        'label' => 'Driver license expiring soon',
        'threshold_days' => 30,
        'permission' => 'drivers.view', // TODO: confirm actual permission name
    ],

    'vehicle_stopped' => [
        'label' => 'Vehicle stopped for a long time',
        // Alert fires when a vehicle has been marked "stopped" for at least this many days.
        'threshold_days' => 30,
        'permission' => 'vehicles.view',
    ],

    'maintenance_overdue' => [
        'label' => 'Maintenance overdue past its end date',
        'permission' => 'maintenance.view',
    ],
];
