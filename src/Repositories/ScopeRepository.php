<?php

namespace SSOfy\Laravel\Repositories;

use SSOfy\Repositories\ScopeRepositoryInterface;
use SSOfy\Models\Entities\ScopeEntity;

class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function all($lang)
    {
        $result = [];

        $scopes = config('ssofy-server.data.scopes');
        foreach ($scopes as $key => $attributes) {
            $attributes['id']    = isset($attributes['id']) ? $attributes['id'] : strval($key);
            $attributes['title'] = __($attributes['title'], [], $lang);

            $result[] = new ScopeEntity($attributes);
        }

        return $result;
    }
}
