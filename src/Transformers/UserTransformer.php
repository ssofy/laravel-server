<?php

namespace SSOfy\Laravel\Transformers;

use SSOfy\Models\Entities\UserEntity;

class UserTransformer
{
    /**
     * @param $user
     * @return UserEntity
     */
    public function transform($user)
    {
        return new UserEntity([
            'id'           => strval($user->{config('ssofy.user.map.id')}),
            'hash'         => strval($user->{config('ssofy.user.map.id')}),
            'display_name' => $user->{config('ssofy.user.map.display_name')},
            'name'         => $user->{config('ssofy.user.map.display_name')},
            'picture'      => null,
            'profile'      => null,
            'email'        => $user->{config('ssofy.user.map.email')},
            'phone'        => $user->{config('ssofy.user.map.phone')},
            'additional'   => $user->toArray(),
        ]);
    }
}
