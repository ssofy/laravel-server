<?php

namespace SSOfy\Laravel\Repositories\Contracts;

use SSOfy\Models\Entities\ClientEntity;

interface ClientRepositoryInterface
{
    /**
     * Get OAuth2 Client by id.
     *
     * @param string $id
     * @return ClientEntity|null
     */
    public function findById($id);
}
