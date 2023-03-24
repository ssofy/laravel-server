<?php

namespace SSOfy\Laravel;

use SSOfy\Client;
use SSOfy\ClientConfig;
use SSOfy\OAuth2Client;
use SSOfy\OAuth2Config;

class Context
{
    /**
     * @return OTP
     */
    public function otp()
    {
        return app(OTP::class);
    }

    /**
     * @param null|ClientConfig $config
     *
     * @return Client
     */
    public function client($config = null)
    {
        if (is_null($config)) {
            $config = $this->defaultClientConfig();
        }

        return new Client($config);
    }

    /**
     * @param null|OAuth2Config $config
     *
     * @return OAuth2Client
     */
    public function oauth2($config = null)
    {
        if (is_null($config)) {
            $config = $this->defaultOAuth2Config();
        }

        return new OAuth2Client($config);
    }

    /**
     * @return ClientConfig
     */
    public function defaultClientConfig()
    {
        $config = config('ssofy', []);

        return new ClientConfig([
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
        $config = config('ssofy.oauth2-client', []);

        $baseUrl = $config['server_url'];

        $authorizeUrl     = \SSOfy\Helper::urlJoin($baseUrl, '/authorize');
        $tokenUrl         = \SSOfy\Helper::urlJoin($baseUrl, '/token');
        $resourceOwnerUrl = \SSOfy\Helper::urlJoin($baseUrl, '/userinfo');

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
            'client_id'          => $config['client_id'],
            'client_secret'      => $config['client_secret'],
            'authorize_url'      => $authorizeUrl,
            'token_url'          => $tokenUrl,
            'resource_owner_url' => $resourceOwnerUrl,
            'redirect_uri'       => $redirectUri,
            'pkce_verification'  => $config['pkce_verification'],
            'pkce_method'        => $config['pkce_method'],
            'timeout'            => $config['timeout'],
            'scopes'             => $config['scopes'],
            'state_store'        => isset($config['state']['store']) ? app(Storage::class, [
                'driver' => $config['state']['store'],
            ]) : null,
            'state_ttl'          => $config['state']['ttl'],
            'session_store'      => app(Session::class),
        ]);
    }
}
