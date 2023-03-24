<?php

namespace SSOfy\Laravel\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use SSOfy\Laravel\Context;
use SSOfy\Laravel\Session;
use SSOfy\OAuth2Config;

class SSOMiddleware
{
    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var OAuth2Config
     */
    private $oauthConfig;

    public function __construct(Auth $auth, Context $context, Session $session)
    {
        $this->auth        = $auth;
        $this->context     = $context;
        $this->oauthConfig = $this->context->defaultOAuth2Config();
    }

    public function handle($request, Closure $next, $modifier = null)
    {
        $authorized = false;

        $oauth2Client = $this->context->oauth2();

        if ($request->acceptsHtml()) {
            $state = $oauth2Client->getSessionState();
        }

        try {
            $authorized = $this->auth->guard()->check();
        } catch (\SSOfy\Exceptions\InvalidTokenException $exception) {
            if (isset($state)) {
                $oauth2Client->deleteState($state);
            }
        } catch (\SSOfy\Exceptions\UserNotFoundException $exception) {
            throw $exception;
        } catch (\SSOfy\Exceptions\Exception $exception) {
            throw $exception;
        }

        if (!$authorized) {
            return $this->next($request, $next, $modifier);
        }

        if (isset($state)) {
            $oldScopes = $oauth2Client->getConfig($state)->getScopes();
            sort($oldScopes);

            $newScopes = $this->oauthConfig->getScopes();
            sort($newScopes);

            if (implode(' ', $oldScopes) !== implode(' ', $newScopes)) {
                return $this->next($request, $next, $modifier);
            }
        }

        return $next($request);
    }

    protected function redirectTo($request)
    {
        if (!$request->isMethod('GET')) {
            return null;
        }

        if ($request->acceptsHtml()) {
            return $this->context->oauth2()->initAuthCodeFlow($request->getRequestUri());
        }

        return null;
    }

    private function next($request, $next, $modifier)
    {
        $passive  = $modifier === 'passive';
        $redirect = $modifier === 'redirect' && $request->acceptsHtml();

        if ($passive) {
            return $next($request);
        } elseif ($redirect) {
            throw new AuthenticationException('401 Unauthorized', [], $this->redirectTo($request));
        } else {
            return response()->make(
                view('vendor.ssofy.error', [
                    'status'      => 401,
                    'title'       => 'Unauthorized',
                    'error'       => '401 Unauthorized',
                    'description' => 'The request requires valid user authentication.',
                ]),
                401
            );
        }
    }
}
