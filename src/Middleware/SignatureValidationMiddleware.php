<?php

namespace SSOfy\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use SSOfy\Laravel\Context;
use SSOfy\SignatureValidator;

class SignatureValidationMiddleware
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

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

        $validator = new SignatureValidator($this->context->defaultAPIConfig());

        if (false === $validator->verifyBase64Signature($path, $params, $signature)) {
            return response('', 400);
        }

        return $next($request);
    }
}
