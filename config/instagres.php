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
    | Default Claim URL Environment Variable
    |--------------------------------------------------------------------------
    |
    | Customize the environment variable name used to store the default claim
    | URL when creating a database with --set-default. Named connections
    | (e.g., --save-as=staging) use {PREFIX}_CLAIM_URL regardless of this.
    |
    */
    'claim_url_var' => 'INSTAGRES_CLAIM_URL',
];
