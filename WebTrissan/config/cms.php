<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Access
    |--------------------------------------------------------------------------
    |
    | Comma-separated list of emails allowed into the Filament panel. When the
    | list is empty, access is only open in the local and testing environments.
    |
    */

    'admin_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CMS_ADMIN_EMAILS', ''))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Seeded Admin Password
    |--------------------------------------------------------------------------
    |
    | Password for the admin user created by DatabaseSeeder. It defaults to
    | "password" in local and testing only; other environments must set it.
    |
    */

    'admin_password' => env('CMS_ADMIN_PASSWORD'),

];
