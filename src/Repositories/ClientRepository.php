<?php

namespace SSOfy\Laravel\Repositories;

use SSOfy\Laravel\Repositories\Contracts\ClientRepositoryInterface;
use SSOfy\Models\Entities\ClientEntity;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findById($id)
    {
        $data = config('ssofy.data.clients');

        if (!isset($data[$id])) {
            return null;
        }

        $result     = new ClientEntity($data[$id]);
        $result->id = strval($id);

        return $result;
    }
}
