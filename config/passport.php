<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Passport Guard
    |--------------------------------------------------------------------------
    |
    | Here you may specify which authentication guard Passport will use when
    | authenticating users. This value should correspond with one of your
    | guards that is already present in your "auth" configuration file.
    |
    */

    'guard' => 'api',

    /*
    |--------------------------------------------------------------------------
    | Passport Password Grant Client
    |--------------------------------------------------------------------------
    |
    | Here you may specify which password grant client to use when
    | authenticating users through the password grant flow. You may
    | define your own password grant clients in the database.
    |
    */

    'password_client' => [
        'id' => env('PASSPORT_PASSWORD_GRANT_CLIENT_ID'),
        'secret' => env('PASSPORT_PASSWORD_GRANT_CLIENT_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Passport Personal Access Client
    |--------------------------------------------------------------------------
    |
    | Here you may specify which personal access client to use when
    | generating personal access tokens. You may define your own
    | personal access clients in the database.
    |
    */

    'personal_access_client' => [
        'id' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID'),
        'secret' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Passport Storage Driver
    |--------------------------------------------------------------------------
    |
    | This configuration option allows you to customize the storage options
    | for Passport, such as the database connection that should be used
    | by Passport's internal database models.
    |
    */

    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Passport Token Expiration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of minutes that access tokens should
    | remain valid. This option controls the default expiration time for
    | all of the application's generated access tokens.
    |
    */

    'tokens_expire_in' => env('PASSPORT_TOKENS_EXPIRE_IN', 15 * 24 * 60), // 15 days

    'refresh_tokens_expire_in' => env('PASSPORT_REFRESH_TOKENS_EXPIRE_IN', 30 * 24 * 60), // 30 days

    'personal_access_tokens_expire_in' => env('PASSPORT_PERSONAL_ACCESS_TOKENS_EXPIRE_IN', 6 * 30 * 24 * 60), // 6 months

];
