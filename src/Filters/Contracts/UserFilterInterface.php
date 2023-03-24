<?php

namespace SSOfy\Laravel\Filters\Contracts;

use SSOfy\Models\Entities\UserEntity;

interface UserFilterInterface
{
    /**
     * @param UserEntity $user
     * @param string[] $scopes
     * @return UserEntity
     */
    public function filter($user, $scopes);
}
