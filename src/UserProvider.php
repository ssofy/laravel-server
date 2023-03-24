<?php

namespace SSOfy\Laravel;

use Illuminate\Contracts\Auth\UserProvider as LaravelUserProvider;
use SSOfy\Client;
use SSOfy\Exceptions\InvalidTokenException;

class UserProvider implements LaravelUserProvider
{
    /**
     * @var bool
     */
    protected $cache;

    /**
     * @var Client
     */
    protected $client;

    public function __construct($providerConfig, Context $context)
    {
        if (isset($providerConfig['cache'])) {
            $this->cache = boolval($providerConfig['cache']);
        } else {
            $this->cache = true;
        }

        $this->client = new Client($context->defaultClientConfig());
    }

    public function retrieveById($identifier)
    {
        $identifier = strval($identifier);

        try {
            $result = $this->client->findUserById($identifier, $this->cache);
        } catch (InvalidTokenException $e) {
            return null;
        }

        if ($result->user->id != $identifier) {
            return null;
        }

        return new User($result->user->toArray());
    }

    public function retrieveByToken($identifier, $token)
    {
        try {
            $result = $this->client->authenticatedUser($token, $this->cache);
        } catch (InvalidTokenException $e) {
            return null;
        }

        if ($result->user->id != $identifier) {
            return null;
        }

        return new User($result->user->toArray());
    }

    public function updateRememberToken($user, $token)
    {
        // not implemented
    }

    public function retrieveByCredentials($credentials)
    {
        return null;
    }

    public function validateCredentials($user, $credentials)
    {
        return false;
    }
}
