<?php

namespace SSOfy\Laravel;

use Carbon\Carbon;
use Illuminate\Contracts\Cache\Store;
use SSOfy\Storage\StorageInterface;

class Storage implements StorageInterface
{
    /**
     * @var Store
     */
    protected $cache;

    /**
     * @param string $driver
     */
    public function __construct($driver)
    {
        $this->cache = app('cache')->store($driver);
    }

    public function put($key, $value, $ttl = 0)
    {
        $ex = null;
        if ($ttl > 0) {
            $ex = Carbon::now()->addSeconds($ttl);
        }

        $this->cache->put($key, $value, $ex);
    }

    public function get($key)
    {
        return $this->cache->get($key);
    }

    public function delete($key)
    {
        $this->cache->forget($key);
    }

    public function flushAll()
    {
        $this->cache->flush();
    }

    public function cleanup()
    {
    }
}
