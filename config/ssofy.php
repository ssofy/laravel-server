<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Config
    |--------------------------------------------------------------------------
    |
    | These credentials are required for the API connection.
    |
    */
    'domain'         => env('SSOFY_DOMAIN', 'us-1.api.ssofy.com'),
    'key'            => env('SSOFY_KEY'),
    'secret'         => env('SSOFY_SECRET'),
    'secure'         => env('SSOFY_SECURE', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Caching can speed up response time by reducing the number of connections
    | to SSOfy. Once a token is deleted, a signal will be emitted to this
    | application to request cache invalidation for the deleted token.
    |
    */
    'cache'          => [
        'store' => env('SSOFY_CACHE_DRIVER', null),
        'ttl'   => env('SSOFY_CACHE_TTL', 10800), // time-to-live in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Settings
    |--------------------------------------------------------------------------
    |
    | Select which authentication methods to be allowed.
    |
    */
    'authentication' => [
        'username'     => true,
        'email'        => true,
        'phone'        => true,
        'token'        => true,
        'passwordless' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    |
    | You may extend the default repositories or replace with your own
    | customized repositories to change how data is stored and retrieved
    | from/to database or authentication providers.
    |
    */
    'repository'     => [
        'client' => \SSOfy\Laravel\Repositories\ClientRepository::class,
        'scope'  => \SSOfy\Laravel\Repositories\ScopeRepository::class,
        'user'   => \SSOfy\Laravel\Repositories\UserRepository::class,
        'otp'    => \SSOfy\Laravel\Repositories\OTPRepository::class,
        'api'    => \SSOfy\Laravel\Repositories\APIRepository::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | User Data Settings
    |--------------------------------------------------------------------------
    */
    'user'           => [
        'model' => \App\Models\User::class, // Required only for "Registration" and "Password Reset" functionalities.

        'filter' => \SSOfy\Laravel\Filters\UserFilter::class,

        /**
         * Specify the actual column name for each of the user claims.
         * Set to null if no column exists in the database for the claim.
         */
        'column' => [
            'id'                => 'id',
            'hash'              => 'id',
            'name'              => 'name',
            'display_name'      => 'name',
            'picture'           => null,
            'username'          => 'username',
            'email'             => 'email',
            'email_verified_at' => 'email_verified_at',
            'phone'             => 'phone',
            'phone_verified_at' => 'phone_verified_at',
            'password'          => 'password',
            'metadata'          => 'metadata',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Settings
    |--------------------------------------------------------------------------
    */
    'otp'            => [
        'store'        => env('SSOFY_OTP_CACHE_DRIVER', 'file'),
        'notification' => [
            'brand' => env('APP_NAME'),

            'email_channel' => env('SSOFY_OTP_EMAIL_CHANNEL', 'mail'),

            // 'nexmo' in older laravel versions
            'sms_chanel'    => env('SSOFY_OTP_SMS_CHANNEL', 'vonage'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fixed Data
    |--------------------------------------------------------------------------
    |
    | Default repositories generate a predefined set of scopes and clients,
    | as specified below.
    |
    | For more complicated scenarios (e.g. read from database), you may need to
    | extend the default repositories by overriding methods to meet your
    | requirements.
    |
    | Read more in docs: https://www.ssofy.com/docs/SDK/Laravel/Repositories
    |
    */
    'data'           => [
        'scopes'  => [
            '*' => [
                'title'       => 'Read and Write all data.',
                'description' => null,
                'icon'        => 'fa-user-shield', // https://fontawesome.com
                'url'         => null,
            ]
        ],
        'clients' => [
            'test' => [
                'name'           => 'My App Name', // required
                'secret'         => 'CLIENT SECRET KEY',
                'redirect_uris'  => ['*'], // wildcard is supported but not recommended.
                'icon'           => null,
                'theme'          => 'default',
                'tos'            => 'https://...',
                'privacy_policy' => 'https://...',
                'confidential'   => false, // https://oauth.net/2/client-types
            ]
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OAuth2 Client Configurations
    |--------------------------------------------------------------------------
    */
    'oauth2-client'  => [
        'client_id'         => env('OAUTH2_CLIENT_ID'),
        'client_secret'     => env('OAUTH2_CLIENT_SECRET'),
        'server_url'        => env('OAUTH2_SERVER_URL'),
        'redirect_uri'      => '/auth/callback',
        'scopes'            => ['*'],
        'timeout'           => 3600, // wait-for-login timeout in seconds
        'pkce_method'       => 'S256', // options: plain, S256
        'pkce_verification' => true,
        'state'             => [
            'store' => env('OAUTH2_STATE_CACHE_DRIVER', 'file'),
            'ttl'   => env('OAUTH2_STATE_CACHE_TTL', 31536000), // time-to-live in seconds (default: 1-year)
        ],
    ],
];
