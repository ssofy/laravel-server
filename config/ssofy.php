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
    'domain'        => env('SSOFY_DOMAIN', 'us-1.api.ssofy.com'),
    'key'           => env('SSOFY_KEY'),
    'secret'        => env('SSOFY_SECRET'),
    'secure'        => env('SSOFY_SECURE', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Use caching to reduce connections to SSOfy and increased response time.
    | Once a token is deleted, a signal will be emitted to this application to
    | request cache invalidation for the deleted token.
    |
    */
    'cache'         => [
        'store' => env('SSOFY_CACHE_DRIVER', null),
        'ttl'   => env('SSOFY_CACHE_TTL', 10800), // time-to-live in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | User Data Settings
    |--------------------------------------------------------------------------
    */
    'user' => [
        'map'    => [
            'id'           => 'id',
            'name'         => 'name',
            'display_name' => 'name',
            'username'     => 'username',
            'email'        => 'email',
            'phone'        => 'phone',
            'password'     => 'password',
        ],
        'filter' => \SSOfy\Laravel\Filters\UserFilter::class,

        // optional: for user creation only
        'model'  => \App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Settings
    |--------------------------------------------------------------------------
    */
    'otp'           => [
        'store'        => env('SSOFY_OTP_CACHE_DRIVER', 'file'),
        'notification' => [
            'brand'         => env('APP_NAME'),

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
    | Default repositories generate a fixed list of scopes and clients as
    | configured here.
    | For complex use cases (e.g. read from database), you may need to extend
    | the default repositories by overriding methods to meet your requirements.
    |
    | Read more in docs: https://www.ssofy.com/docs/SDK/Laravel/Repositories
    |
    */
    'data'          => [
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
    'oauth2-client' => [
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

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    */
    'repository'    => [
        'client' => \SSOfy\Laravel\Repositories\ClientRepository::class,
        'scope'  => \SSOfy\Laravel\Repositories\ScopeRepository::class,
        'user'   => \SSOfy\Laravel\Repositories\UserRepository::class,
        'otp'    => \SSOfy\Laravel\Repositories\OTPRepository::class,
        'api'    => \SSOfy\Laravel\Repositories\APIRepository::class,
    ],
];
