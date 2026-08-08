<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Theme
    |--------------------------------------------------------------------------
    |
    | The theme used when the visitor has no theme cookie yet.
    |
    */

    'default' => 'light',

    /*
    |--------------------------------------------------------------------------
    | Available Themes
    |--------------------------------------------------------------------------
    |
    | Each key must have a matching [data-theme="<key>"] block in
    | resources/css/app.css and a hardcoded Arabic label in
    | app/Providers/AppServiceProvider.php ($labels).
    |
    */

    'themes' => [
        'light',
        'dark',
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Cookie
    |--------------------------------------------------------------------------
    |
    | The cookie used to persist the visitor's theme choice across visits.
    |
    */

    'cookie' => 'theme',

];
