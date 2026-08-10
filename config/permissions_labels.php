<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permission labels
    |--------------------------------------------------------------------------
    |
    | Human-readable Arabic labels used when displaying the permission
    | catalogue in the UI. Group labels are the feature areas from
    | config/permissions.php; permission labels map each permission key to its
    | display name.
    |
    | The keys here are only for display — the raw keys from
    | config/permissions.php are what gets stored in the database.
    |
    */

    'groups' => [
        'users' => 'المستخدمون',
        'roles' => 'الأدوار',
        'settings' => 'الإعدادات',
        'managements' => 'الإدارات',
        'departments' => 'الأقسام',
        'vehicles' => 'المركبات',
        'drivers' => 'السائقون',
        'odometer' => 'العداد',
    ],

    'permissions' => [
        'users.view' => 'عرض المستخدمين',
        'users.create' => 'إضافة مستخدم',
        'users.edit' => 'تعديل مستخدم',
        'users.delete' => 'حذف مستخدم',

        'roles.view' => 'عرض الأدوار',
        'roles.create' => 'إضافة دور',
        'roles.edit' => 'تعديل دور',
        'roles.delete' => 'حذف دور',

        'settings.edit' => 'تعديل الإعدادات',

        'managements.view' => 'عرض الإدارات',
        'managements.create' => 'إضافة إدارة',
        'managements.edit' => 'تعديل إدارة',
        'managements.delete' => 'حذف إدارة',

        'departments.view' => 'عرض الأقسام',
        'departments.create' => 'إضافة قسم',
        'departments.edit' => 'تعديل قسم',
        'departments.delete' => 'حذف قسم',

        'vehicles.view' => 'عرض المركبات',
        'vehicles.create' => 'إضافة مركبة',
        'vehicles.edit' => 'تعديل مركبة',
        'vehicles.delete' => 'حذف مركبة',
        'vehicles.assign' => 'تعيين مركبة',
        'vehicles.end-assignment' => 'إنهاء التعيين',

        'drivers.view' => 'عرض السائقين',
        'drivers.create' => 'إضافة سائق',
        'drivers.edit' => 'تعديل سائق',
        'drivers.delete' => 'حذف سائق',

        'odometer.update' => 'تحديث قراءة العداد',
        'odometer.correct' => 'تصحيح قراءة العداد',
    ],

];
