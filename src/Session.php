<?php

namespace SSOfy\Laravel;

use Illuminate\Contracts\Session\Session as RequestSession;
use SSOfy\Storage\StorageInterface;

class Session implements StorageInterface
{
    /**
     * @var RequestSession
     */
    private $session;

    public function __construct(RequestSession $session)
    {
        $this->session = $session;
    }

    public function put($key, $value, $ttl = 0)
    {
        $this->session->put($key, $value);
    }

    public function get($key)
    {
        return $this->session->get($key);
    }

    public function delete($key)
    {
        $this->session->remove($key);
    }

    public function flushAll()
    {
        $this->session->flush();
    }

    public function cleanup()
    {
        // not implemented
    }
}
