<?php

namespace SSOfy\Laravel\Transformers;

use Illuminate\Support\Arr;
use SSOfy\Models\Entities\UserEntity;

class UserTransformer
{
    /**
     * @param $user
     *
     * @return UserEntity
     */
    public function transform($user)
    {
        $metadata = $this->get($user, 'metadata', []);

        if (!is_array($metadata)) {
            $metadata = json_decode($metadata, true);
            if (is_null($metadata)) {
                $metadata = [];
            }
        }

        $metadataColumn = config('ssofy.user.column.metadata');

        return new UserEntity(
            array_merge($metadata, [
                'id'                => strval($this->get($user, 'id')),
                'hash'              => strval($this->get($user, 'hash')),
                'name'              => $this->get($user, 'name'),
                'display_name'      => $this->get($user, 'display_name'),
                'picture'           => $this->get($user, 'picture'),
                'username'          => $this->get($user, 'username'),
                'email'             => $this->get($user, 'email'),
                'email_verified_at' => $this->get($user, 'email_verified_at'),
                'phone'             => $this->get($user, 'phone'),
                'phone_verified_at' => $this->get($user, 'phone_verified_at'),
                'additional'        => Arr::except($user->toArray(), $metadataColumn),
            ])
        );
    }

    private function get($user, $column, $default = null)
    {
        $mappedColumn = config('ssofy.user.column.' . $column);

        if (is_null($mappedColumn) || trim($mappedColumn) === '') {
            return null;
        }

        return object_get($user, $mappedColumn, $default);
    }
}
