<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSOfy OAuth2 Client Settings
    |--------------------------------------------------------------------------
    */
    'oauth2' => [
        'url'               => env('SSOFY_URL'),
        'client_id'         => env('SSOFY_CLIENT_ID'),
        'client_secret'     => env('SSOFY_CLIENT_SECRET'),
        'redirect_uri'      => '/sso/callback',
        'scopes'            => ['*'],
        'locale'            => null, // 'en',
        'timeout'           => 3600, // wait-for-login timeout in seconds
        'pkce_method'       => 'S256', // options: plain, S256
        'pkce_verification' => true,
        'state'             => [
            'store' => env('SSOFY_STATE_CACHE_DRIVER', 'file'),
            'ttl'   => env('SSOFY_STATE_CACHE_TTL', 31536000), // time-to-live in seconds (default: 1-year)
        ],
    ],
];
