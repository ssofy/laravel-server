<?php

namespace SSOfy\Laravel\Repositories;

use SSOfy\Laravel\Context;
use SSOfy\Laravel\Repositories\Contracts\APIRepositoryInterface;

class APIRepository implements APIRepositoryInterface
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function deleteToken($token)
    {
        $this->context->client()->invalidateTokenCache($token);
    }

    /**
     * @inheritDoc
     */
    public function deleteAllTokens()
    {
        $this->context->client()->purgeTokenCache();
    }
}
