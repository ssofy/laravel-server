<?php

namespace SSOfy\Laravel\Traits;

use SSOfy\Laravel\Rules\UserEntityValidation;
use SSOfy\Laravel\Rules\UserFiltersValidation;
use SSOfy\Laravel\Rules\UserSortsValidation;

trait Rules
{
    /**
     * @return array
     */
    protected function passwordAuthRules()
    {
        return [
            'method'        => ['required', 'string', 'in:username,email,phone,otp'],
            'identifier'    => ['required', 'string'],
            'password'      => ['nullable', 'string'],
            'request_token' => ['nullable', 'boolean'],
            'ip'            => ['nullable', 'ip'],
        ];
    }

    /**
     * @return array
     */
    protected function tokenAuthRules()
    {
        return [
            'token' => ['required', 'string'],
            'ip'    => ['nullable', 'ip'],
        ];
    }

    /**
     * @return array
     */
    protected function socialAuthRules()
    {
        return [
            'user'     => [UserEntityValidation::make(true)],
            'provider' => ['required', 'string'],
            'ip'       => ['nullable', 'ip'],
        ];
    }

    /**
     * @return array
     */
    protected function otpOptionsRules()
    {
        return [
            'action'     => ['required', 'string', 'in:authentication,password_reset,password_renew'],
            'method'     => ['required', 'string', 'in:username,email,phone'],
            'identifier' => ['required', 'string'],
            'ip'         => ['nullable', 'ip'],
        ];
    }

    /**
     * @return array
     */
    public function eventRules()
    {
        return [
            'action'  => ['required', 'string', 'min:1'],
            'payload' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array
     */
    public function scopeResourceRules()
    {
        return [
            'lang' => ['nullable', 'string', 'max:5'],
        ];
    }

    /**
     * @return array
     */
    public function clientResourceRules()
    {
        return [
            'id' => ['required', 'string', 'min:1'],
        ];
    }

    /**
     * @return array
     */
    public function userResourceRules()
    {
        return [
            'id'       => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'email'    => ['nullable', 'string'],
            'phone'    => ['nullable', 'string'],
            'scopes'   => ['nullable', 'array'],
            'scopes.*' => ['string'],
            'ip'       => ['nullable', 'ip'],
        ];
    }

    /**
     * @return array
     */
    public function usersResourceRules()
    {
        return [
            'filters'   => ['nullable', 'array'],
            'filters.*' => ['array', UserFiltersValidation::make()],
            'sorts'     => ['nullable', 'array'],
            'sorts.*'   => ['array', UserSortsValidation::make()],
            'page'      => ['nullable', 'integer', 'min:1'],
            'count'     => ['nullable', 'integer', 'min:1', 'max:100'],
            'ip'        => ['nullable', 'ip'],
        ];
    }
}
