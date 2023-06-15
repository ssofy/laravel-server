<?php

namespace SSOfy\Laravel\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SSOfy\Laravel\Context;
use SSOfy\Laravel\Traits\Validation;

class OAuthClientController extends AbstractController
{
    use Validation;

    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /*
     ------------------------------------------------------------
      PUBLIC METHODS
     ------------------------------------------------------------
     */

    public function handleRedirectBack(Request $request)
    {
        /*
         * Validation
         */
        $this->validateOAuth2ClientRedirectRequest($request);

        /*
         * Params
         */
        $state            = $request->input('state');
        $code             = $request->input('code');
        $error            = $request->input('error');
        $errorDescription = $request->input('error_description');

        if (!empty($error)) {
            return view('vendor/ssofy/error', [
                'status'      => $code,
                'title'       => $error,
                'error'       => str_replace('_', ' ', Str::title($error)),
                'description' => $errorDescription,
            ]);
        }

        /*
         * Redirection
         */
        $url = $this->context->ssoClient()->continueAuthCodeFlow($state, $code);

        return redirect($url);
    }

    public function logout(Request $request)
    {
        $redirectUri = $request->input('redirect_uri', null);
        $everywhere  = boolval($request->input('everywhere', false));

        $ssoClient = $this->context->ssoClient();

        $sessionState = $ssoClient->getSessionState();
        if (empty($sessionState)) {
            $ssoClient->deleteState($sessionState);
        }

        return redirect($ssoClient->getLogoutUrl($redirectUri, $everywhere));
    }

    public function socialAuth(Request $request, $provider)
    {
        $redirectUri = $request->input('redirect_uri', url()->to('/'));

        return redirect($this->context->ssoClient()->initSocialAuthCodeFlow($redirectUri, $provider));
    }
}
