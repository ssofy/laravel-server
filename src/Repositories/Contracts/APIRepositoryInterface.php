<?php

namespace SSOfy\Laravel\Repositories\Contracts;

interface APIRepositoryInterface
{
    /**
     * Delete/Invalidate a cached token
     *
     * @param string $token
     * @return void
     */
    public function deleteToken($token);

    /**
     * Delete/Invalidate all cached tokens
     *
     * @return void
     */
    public function deleteAllTokens();
}
