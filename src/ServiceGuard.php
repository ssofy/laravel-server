<?php

namespace SSOfy\Laravel;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use SSOfy\APIClient;
use SSOfy\Exceptions\APIException;
use SSOfy\Exceptions\InvalidTokenException;
use SSOfy\Exceptions\SignatureVerificationException;
use SSOfy\Models\Token;

class ServiceGuard implements Guard
{
    use GuardHelpers;

    const DRIVER_NAME = 'ssofy';

    /**
     * @var User
     */
    protected $user;

    /**
     * @var UserProvider
     */
    protected $provider;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var APIClient
     */
    protected $client;

    /**
     * @var \SSOfy\OAuth2Client
     */
    protected $oauth2Client;

    /**
     * @var Token
     */
    protected $token;

    public function __construct(UserProvider $provider, Request $request, Context $context)
    {
        $this->provider = $provider;
        $this->request  = $request;
        $this->context  = $context;

        $this->client = new APIClient($context->defaultAPIConfig());

        $this->oauth2Client = $context->ssoClient();
    }

    public function check()
    {
        return !is_null($this->token());
    }

    public function guest()
    {
        return !$this->check();
    }

    public function hasUser()
    {
        return !is_null($this->user());
    }

    public function id()
    {
        $user = $this->user();

        if (is_null($user)) {
            return null;
        }

        return $user->getAuthIdentifier();
    }

    /**
     * @return Authenticatable|null
     * @throws APIException
     * @throws IdentityProviderException
     * @throws SignatureVerificationException
     */
    public function user()
    {
        if (isset($this->user)) {
            return $this->user;
        }

        $token = $this->token();

        if (is_null($token)) {
            return null;
        }

        $user = $this->provider->retrieveById($token->user_id);

        $this->setUser($user);

        if (is_null($user)) {
            return null;
        }

        return $user;
    }

    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
        return $this;
    }

    public function forgetUser()
    {
        $this->user = null;
        return $this;
    }

    /**
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return false;
    }

    /**
     * @throws APIException
     * @throws SignatureVerificationException
     * @throws IdentityProviderException
     */
    public function token()
    {
        if ($this->request->acceptsHtml()) {
            $state       = $this->oauth2Client->getSessionState();
            $accessToken = $this->oauth2Client->getAccessToken($state);
        } else {
            $accessToken = $this->request->header('Authorization');
        }

        if (empty($accessToken)) {
            return null;
        }

        try {
            $result      = $this->client->verifyAuthentication($accessToken);
            $this->token = $result->token;
        } catch (InvalidTokenException $e) {
            return null;
        }

        return $this->token;
    }
}
