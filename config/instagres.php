<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Referrer
    |--------------------------------------------------------------------------
    |
    | This value is used as the referrer when creating databases. It helps
    | identify where database creation requests are coming from. You can
    | customize this to your application name or use the default.
    |
    */
    'referrer' => env('INSTAGRES_REFERRER', 'laravel-instagres'),

    /*
    |--------------------------------------------------------------------------
    | Auto Configure
    |--------------------------------------------------------------------------
    |
    | When enabled, the package will attempt to automatically configure
    | your Laravel database connection when creating a new database via
    | the Artisan command. Disable this if you prefer manual configuration.
    |
    */
    'auto_configure' => env('INSTAGRES_AUTO_CONFIGURE', false),

    /*
    |--------------------------------------------------------------------------
    | Claim URL Environment Variable
    |--------------------------------------------------------------------------
    |
    | Customize the environment variable name used to store the claim URL
    | when creating a database. The claim URL contains everything needed
    | to claim and access your database.
    |
    */
    'claim_url_var' => 'INSTAGRES_CLAIM_URL',
];
