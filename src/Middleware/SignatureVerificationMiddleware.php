<?php

namespace SSOfy\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use SSOfy\SignatureGenerator;
use SSOfy\SignatureVerifier;

class SignatureVerificationMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $signature = $request->header('Signature');

        if (empty($signature)) {
            return response('', 400);
        }

        $path   = '/' . ltrim($request->path(), '/');
        $params = $request->input();
        $secret = config('ssofy-server.secret');

        $signatureGenerator = new SignatureGenerator();
        $validator          = new SignatureVerifier($signatureGenerator);

        if (false === $validator->verifyBase64Signature($path, $params, $secret, $signature)) {
            return response('', 400);
        }

        return $next($request);
    }
}
