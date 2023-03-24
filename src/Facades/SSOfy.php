<?php

namespace SSOfy\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use SSOfy\ClientConfig;
use SSOfy\Laravel\OTP;
use SSOfy\OAuth2Client;
use SSOfy\OAuth2Config;

/**
 * @method static OTP otp()
 * @method static OAuth2Client oauth2()
 * @method static OAuth2Config defaultOAuth2Config()
 * @method static ClientConfig defaultClientConfig()
 */
class SSOfy extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ssofy';
    }
}
