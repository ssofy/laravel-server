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
        return new UserEntity(
            [
                'id'             => $user->id,
                'hash'           => $user->hash,
                'display_name'   => $user->display_name,
                'name'           => $user->name,
                'picture'        => $user->picture,
                'profile'        => $user->profile,
                'username'       => $user->username,
                'email'          => $user->email,
                'email_verified' => $user->email_verified,
                'phone'          => $user->phone,
                'phone_verified' => $user->phone_verified,
                'given_name'     => $user->given_name,
                'middle_name'    => $user->middle_name,
                'family_name'    => $user->family_name,
                'nickname'       => $user->nickname,
                'website'        => $user->website,
                'gender'         => $user->gender,
                'birthdate'      => $user->birthdate,
                'address'        => $user->address,
                'location'       => $user->location,
                'zoneinfo'       => $user->zoneinfo,
                'locale'         => $user->locale,
                'custom_1'       => $user->custom_1,
                'custom_2'       => $user->custom_2,
                'custom_3'       => $user->custom_3,
                'custom_4'       => $user->custom_4,
                'custom_5'       => $user->custom_5,
                'custom_6'       => $user->custom_6,
                'custom_7'       => $user->custom_7,
                'custom_8'       => $user->custom_8,
                'custom_9'       => $user->custom_9,
                //'additional'     => $user->additional
            ]
        );
    }
}
