<?php

namespace SSOfy\Laravel;

use Illuminate\Support\Str;
use SSOfy\Storage\StorageInterface;

class OTP
{
    /**
     * @var StorageInterface
     */
    private $store;

    public function __construct()
    {
        $this->store = app('cache')->store(config('ssofy.otp.store'));
    }

    /**
     * @param int|string $userId
     * @param int $ttl time-to-live in seconds
     * @return string
     */
    public function randomStringOTP($userId, $ttl = 60, $length = 32, $group = null)
    {
        $token = Str::random($length);

        $this->store->put($this->cacheKey($token, $group), $userId, $ttl);

        return $token;
    }

    /**
     * @param int|string $userId
     * @param int $ttl time-to-live in seconds
     * @return string
     */
    public function randomDigitsOTP($userId, $ttl = 60, $digits = 6, $group = null)
    {
        $token = '';

        for ($i = 0; $i < $digits; $i++) {
            $token .= rand(0, 9);
        }

        $this->store->put($this->cacheKey($token, $group), $userId, $ttl);

        return $token;
    }

    /**
     * @param string $token
     * @return false|int
     */
    public function verify($token, $group = null, $forget = false)
    {
        $userId = $this->store->get($this->cacheKey($token, $group));

        if (is_null($userId)) {
            return false;
        }

        if ($forget) {
            $this->forget($token, $group);
        }

        return $userId;
    }

    /**
     * @param string $token
     * @return void
     */
    public function forget($token, $group = null)
    {
        $this->store->delete($this->cacheKey($token, $group));
    }

    private function cacheKey($token, $group = null)
    {
        return "otp:token:$group:$token";
    }
}
