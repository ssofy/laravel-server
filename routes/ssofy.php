<?php

/*
|--------------------------------------------------------------------------
| Resource Endpoints
|--------------------------------------------------------------------------
|
| SSOfy uses these endpoints to query data and send application events.
| Point to these endpoints within your application configuration in the
| SSOfy panel.
|
*/

$router->group([
    'namespace'  => 'SSOfy\Laravel\Controllers',
    'prefix'     => '/external/ssofy/',
    'middleware' => ['ssofy.signature', 'ssofy.response']
], function () use ($router) {

    $router->post('client', 'OAuthServerController@client');

    $router->post('scopes', 'OAuthServerController@scopes');

    $router->post('user', 'OAuthServerController@user');

    $router->post('otp-options', 'AuthController@otpOptions');

    $router->post('auth/password', 'AuthController@passwordAuth');
    $router->post('auth/token', 'AuthController@tokenAuth');
    $router->post('auth/social', 'AuthController@socialAuth');

    $router->post('event', 'EventController@handle');

});

/*
|--------------------------------------------------------------------------
| OAuth2 Callback Handler
|--------------------------------------------------------------------------
*/
$router->get('/auth/callback', [
    'uses' => 'SSOfy\Laravel\Controllers\OAuthClientController@handleRedirectBack',
])->middleware('web');
