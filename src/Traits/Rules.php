<?php

namespace SSOfy\Laravel\Traits;

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
            'provider'   => ['required', 'string'],
            'user.id'    => ['required', 'string', 'min:1'],
            'user.email' => ['required', 'email'],
            'user.name'  => ['nullable', 'string'],
            'ip'         => ['nullable', 'ip'],
        ];
    }

    /**
     * @return array
     */
    protected function otpOptionsRules()
    {
        return [
            'action'     => ['required', 'string', 'in:authorization,password_reset'],
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
    public function scopeEntitiesRules()
    {
        return [
            'lang' => ['nullable', 'string', 'max:2'],
        ];
    }

    /**
     * @return array
     */
    public function clientEntityRules()
    {
        return [
            'id' => ['required', 'string', 'min:1'],
        ];
    }

    /**
     * @return array
     */
    public function userEntityRules()
    {
        return [
            'id'       => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'email'    => ['nullable', 'string'],
            'phone'    => ['nullable', 'string'],
            'scopes'   => ['nullable', 'array'],
            'scopes.*' => ['string'],
        ];
    }

    /**
     * @return array
     */
    public function oauth2ClientRedirectRules()
    {
        return [
            'state'             => ['required', 'string', 'min:1'],
            'code'              => ['string', 'min:1'],
            'error'             => ['string'],
            'error_description' => ['string'],
        ];
    }
}
