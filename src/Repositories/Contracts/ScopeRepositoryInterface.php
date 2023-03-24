<?php

namespace SSOfy\Laravel\Repositories\Contracts;

use SSOfy\Models\Entities\ScopeEntity;

interface ScopeRepositoryInterface
{
    /**
     * Get the list of available OAuth2 Scopes.
     *
     * @param string $lang
     * @return ScopeEntity[]
     */
    public function findAll($lang);
}
