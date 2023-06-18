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

        if (!config('ssofy-server.authentication.methods.' . $validated['method'], false)) {
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
        return $request->validate($this->tokenAuthRules(), $request->input());
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function validateSocialAuthRequest(Request $request)
    {
        return $request->validate($this->socialAuthRules(), $request->input());
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
    public function validateScopeEntitiesRequest(Request $request)
    {
        return $request->validate($this->scopeEntitiesRules(), $request->input());
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validateClientEntityRequest(Request $request)
    {
        return $request->validate($this->clientEntityRules(), $request->input());
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validateUserEntityRequest(Request $request)
    {
        return $request->validate($this->userEntityRules(), $request->input());
    }
}
