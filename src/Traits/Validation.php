<?php

namespace SSOfy\Laravel\Traits;

use Illuminate\Http\Request;

trait Validation
{
    use Rules;

    /**
     * @param Request $request
     * @return array
     */
    protected function validatePasswordAuthRequest(Request $request)
    {
        $validated = $request->validate($this->passwordAuthRules(), $request->input());

        if (!in_array($validated['method'], config('ssofy-server.authentication.methods'))
            && !config('ssofy-server.authentication.methods.' . $validated['method'], false)
        ) {
            abort(401, 'Unauthorized');
        }

        if (empty($validated['password']) && !config('ssofy-server.authentication.passwordless', false)) {
            abort(401, 'Unauthorized');
        }

        return $validated;
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function validateTokenAuthRequest(Request $request)
    {
        $validated = $request->validate($this->tokenAuthRules(), $request->input());

        if (!in_array('token', config('ssofy-server.authentication.methods'))
            && !config('ssofy-server.authentication.methods.token', false)
        ) {
            abort(401, 'Unauthorized');
        }

        return $validated;
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function validateSocialAuthRequest(Request $request)
    {
        $validated = $request->validate($this->socialAuthRules(), $request->input());

        if (!in_array('social', config('ssofy-server.authentication.methods'))
            && !config('ssofy-server.authentication.methods.social', false)
        ) {
            abort(401, 'Unauthorized');
        }

        return $validated;
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function validateOTPOptionsRequest(Request $request)
    {
        return $request->validate($this->otpOptionsRules(), $request->input());
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validateEventRequest(Request $request)
    {
        return $request->validate($this->eventRules(), $request->input());
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validateScopeResourceRequest(Request $request)
    {
        return $request->validate($this->scopeResourceRules(), $request->input());
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validateClientResourceRequest(Request $request)
    {
        return $request->validate($this->clientResourceRules(), $request->input());
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validateUserResourceRequest(Request $request)
    {
        return $request->validate($this->userResourceRules(), $request->input());
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validateUsersResourceRequest(Request $request)
    {
        return $request->validate($this->usersResourceRules(), $request->input());
    }
}
