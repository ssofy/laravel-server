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
        $url = $this->context->oauth2()->continueAuthCodeFlow($state, $code);

        return redirect($url);
    }
}
