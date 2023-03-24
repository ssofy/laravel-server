<?php

namespace SSOfy\Laravel\Filters;

use SSOfy\Laravel\Filters\Contracts\UserFilterInterface;
use SSOfy\Models\Entities\UserEntity;

class UserFilter implements UserFilterInterface
{
    /**
     * @inheritDoc
     */
    public function filter($user, $scopes)
    {
        return new UserEntity([
            'id'    => $user->id,
            'hash'  => $user->hash,
            'email' => $user->email,
        ]);
    }
}
