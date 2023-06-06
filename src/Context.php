<?php

namespace SSOfy\Laravel;

use SSOfy\APIClient;
use SSOfy\APIConfig;
use SSOfy\OAuth2Client;
use SSOfy\OAuth2Config;

class Context
{
    /**
     * @return UserTokenManager
     */
    public function userTokenManager()
    {
        return app(UserTokenManager::class);
    }

    /**
     * @param null|APIConfig $config
     * @return APIClient
     */
    public function apiClient($config = null)
    {
        if (is_null($config)) {
            $config = $this->defaultAPIConfig();
        }

        return new APIClient($config);
    }

    /**
     * @param null|OAuth2Config $config
     * @return OAuth2Client
     */
    public function ssoClient($config = null)
    {
        if (is_null($config)) {
            $config = $this->defaultOAuth2Config();
        }

        return new OAuth2Client($config);
    }

    /**
     * @return APIConfig
     */
    public function defaultAPIConfig()
    {
        $config = config('ssofy', []);

        return new APIConfig([
            'domain'      => $config['domain'],
            'key'         => $config['key'],
            'secret'      => $config['secret'],
            'secure'      => $config['secure'],
            'cache_store' => isset($config['cache']['store']) ? app(Storage::class, [
                'driver' => $config['cache']['store']
            ]) : null,
            'cache_ttl'   => $config['cache']['ttl'],
        ]);
    }

    /**
     * @return OAuth2Config
     */
    public function defaultOAuth2Config()
    {
        $config = config('sso-client.oauth2', []);

        $redirectUri = $config['redirect_uri'];
        if (!is_null($redirectUri)
            && !(
                str_starts_with($redirectUri, 'http://')
                || str_starts_with($redirectUri, 'https://')
                || str_starts_with($redirectUri, '//')
            )
        ) {
            $redirectUri = app('url')->to($config['redirect_uri']);
        }

        return new OAuth2Config([
            'url'               => $config['url'],
            'client_id'         => $config['client_id'],
            'client_secret'     => $config['client_secret'],
            'redirect_uri'      => $redirectUri,
            'pkce_verification' => $config['pkce_verification'],
            'pkce_method'       => $config['pkce_method'],
            'timeout'           => $config['timeout'],
            'scopes'            => $config['scopes'],
            'state_store'       => isset($config['state']['store']) ? app(Storage::class, [
                'driver' => $config['state']['store'],
            ]) : null,
            'state_ttl'         => $config['state']['ttl'],
            'session_store'     => app(Session::class),
        ]);
    }
}
