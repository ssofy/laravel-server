<?php

namespace SSOfy\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use SSOfy\APIConfig;
use SSOfy\Laravel\UserTokenManager;
use SSOfy\OAuth2Client;
use SSOfy\OAuth2Config;

/**
 * @method static UserTokenManager userTokenManager()
 * @method static OAuth2Client apiClient()
 * @method static OAuth2Client oauth2Client()
 * @method static APIConfig defaultAPIConfig()
 * @method static OAuth2Config defaultOAuth2Config()
 */
class SSOfy extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ssofy';
    }
}
