<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurations
    |--------------------------------------------------------------------------
    */
    'secret' => env('SSOFY_API_SECRET'),

    'event_queue' => env('SSOFY_EVENT_QUEUE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | OTP Settings
    |--------------------------------------------------------------------------
    |
    | Specify the cache driver to be used as temporary token storage, including
    | Action, OTP, and Auth tokens.
    |
    | Also, specify the Email and SMS channels to use when the user requests a
    | new OTP.
    |
    */
    'otp'            => [
        'store'        => env('SSOFY_OTP_CACHE_DRIVER', 'file'),
        'notification' => [
            'class' => SSOfy\Laravel\Notifications\OTPNotification::class,

            'email_channel' => env('SSOFY_OTP_EMAIL_CHANNEL', 'mail'),

            // 'nexmo' in older laravel versions
            'sms_chanel'    => env('SSOFY_OTP_SMS_CHANNEL', 'vonage'),

            'settings' => [
                'brand' => env('APP_NAME'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Settings
    |--------------------------------------------------------------------------
    |
    | Select which authentication methods to be enabled in server.
    |
    */
    'authentication' => [
        'methods'      => [
            'username' => true,
            'email'    => true,
            'phone'    => true,
            'token'    => true,
            'otp'      => true,
        ],
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
    ],

    /*
    |--------------------------------------------------------------------------
    | User Data Settings
    |--------------------------------------------------------------------------
    */
    'user'           => [
        'model' => \App\Models\User::class,

        'filter'  => \SSOfy\Laravel\Filters\UserFilter::class,

        /**
         * Specify the actual column name for each of the user claims.
         * Set to null if no column exists in the database for the claim.
         *
         * "[CLAIM]" => "[COLUMN]"
         */
        'columns' => [
            'id'                => 'id',
            'hash'              => 'id',
            'name'              => 'name',
            'display_name'      => 'name',
            'picture'           => 'avatar_url',
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
    | Static Data
    |--------------------------------------------------------------------------
    |
    | Default repositories generate a predefined set of scopes and clients,
    | as specified below.
    |
    | For more complicated scenarios (e.g. read from database), you may need to
    | extend the default repositories by overriding methods to meet your
    | requirements.
    |
    | Read more in docs: https://www.ssofy.com/docs/SDK/LaravelServer/Repositories
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
];
