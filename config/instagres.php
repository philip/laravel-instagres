<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API ref (Claimable Postgres)
    |--------------------------------------------------------------------------
    |
    | Optional override sent to the API as "ref". When empty, "referrer" below
    | is used. INSTAGRES_REF matches the API name; INSTAGRES_REFERRER is kept
    | for backward compatibility with published configs.
    |
    */
    'ref' => env('INSTAGRES_REF'),

    /*
    |--------------------------------------------------------------------------
    | Referrer
    |--------------------------------------------------------------------------
    |
    | Default API ref when "ref" is not set. Identifies where create requests
    | originate. You can customize this to your application name.
    |
    */
    'referrer' => env('INSTAGRES_REFERRER', 'laravel-instagres'),

    /*
    |--------------------------------------------------------------------------
    | Logical replication
    |--------------------------------------------------------------------------
    |
    | When true, new databases are created with logical replication enabled.
    | The instagres:create --logical-replication flag ORs with this value.
    |
    */
    'logical_replication' => filter_var(env('INSTAGRES_LOGICAL_REPLICATION', false), FILTER_VALIDATE_BOOLEAN),

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
