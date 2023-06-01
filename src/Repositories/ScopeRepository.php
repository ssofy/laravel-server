<?php

namespace SSOfy\Laravel\Repositories;

use SSOfy\Laravel\Repositories\Contracts\ScopeRepositoryInterface;
use SSOfy\Models\Entities\ScopeEntity;

class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findAll($lang)
    {
        $result = [];

        $data = config('ssofy.data.scopes');
        foreach ($data as $id => $properties) {
            $scope        = new ScopeEntity($properties);
            $scope->id    = strval($id);
            $scope->title = __($scope->title, [], $lang);
            $result[]     = $scope;
        }

        return $result;
    }
}
