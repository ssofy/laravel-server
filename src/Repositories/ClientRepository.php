<?php

namespace SSOfy\Laravel\Repositories;

use SSOfy\Repositories\ClientRepositoryInterface;
use SSOfy\Models\Entities\ClientEntity;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findById($id)
    {
        $clients = config('ssofy-server.data.clients');

        $client = null;
        foreach ($clients as $key => $attributes) {
            $key = isset($attributes['id']) ? $attributes['id'] : strval($key);
            if ($key === $id) {
                $client       = $attributes;
                $client['id'] = $key;
                break;
            }
        }

        if (is_null($client)) {
            return null;
        }

        return new ClientEntity($client);
    }
}
