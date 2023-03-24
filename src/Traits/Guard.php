<?php

namespace SSOfy\Laravel\Traits;

use Illuminate\Support\Facades\Auth;
use SSOfy\Laravel\ServiceGuard;

trait Guard
{
    /**
     * @return \Illuminate\Contracts\Auth\Guard
     */
    protected function guard()
    {
        return Auth::guard($this->findGuard());
    }

    /**
     * @return string
     */
    protected function findGuard()
    {
        $guards = config('auth.guards');

        foreach ($guards as $guard => $settings) {
            if ($settings['driver'] == ServiceGuard::DRIVER_NAME) {
                return $guard;
            }
        }

        return null;
    }
}
