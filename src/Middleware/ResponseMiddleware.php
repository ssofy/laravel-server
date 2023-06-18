<?php

namespace SSOfy\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SSOfy\Exceptions\Exception;
use SSOfy\Helper;
use SSOfy\Models\BaseModel;
use SSOfy\SignatureGenerator;

class ResponseMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     * @throws Exception
     */
    public function handle($request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        /** @var Response $response */
        $response = $next($request);

        if ($response->headers->has('Signature')) {
            // skip if signature was already set
            return $response;
        }

        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $original = $response->original;

        if (is_array($original)) {
            $body = array_map(function ($scope) {
                return $this->rewrite($scope);
            }, $original);
        } else {
            $body = $this->rewrite($original);
        }

        if (!is_array($body)) {
            throw new Exception('Cannot generate signature for non-array payload.');
        }

        $signatureGenerator = new SignatureGenerator();

        $path   = '/' . ltrim($request->path(), '/');
        $secret = config('ssofy-server.secret');
        $salt   = Helper::randomString(rand(16, 32));

        return $response
            ->setContent(json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->withHeaders([
                'Signature' => base64_encode(json_encode($signatureGenerator->generate($path, $body, $secret, $salt)->toArray())),
            ]);
    }

    private function rewrite($original)
    {
        if (!is_a($original, BaseModel::class)) {
            return $original;
            // throw new Exception(sprintf('Expected %s but got %s', BaseModel::class, gettype($original)));
        }

        return $original->toArray();
    }
}
