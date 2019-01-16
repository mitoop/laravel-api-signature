<?php

namespace Mitoop\ApiSignature;

use Illuminate\Contracts\Foundation\Application;
use Mitoop\ApiSignature\Exception\InvalidAppIdException;
use Mitoop\ApiSignature\Exception\InvalidSignatureException;
use Mitoop\ApiSignature\Facades\Signature;

class SignatureMiddleware
{

    /**
     * The Laravel Application
     */
    protected $app;

    /**
     * Create a new middleware instance.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param          $request
     * @param \Closure $next
     *
     * @return
     * @throws InvalidAppIdException
     * @throws InvalidSignatureException
     */
    public function handle($request, \Closure $next)
    {
        $appId = $request->query('app_id');
        if (\is_null($appId)) {
            throw new InvalidAppIdException('app_id is lost.');
        }

        $clients = $this->app['config']->get('api-signature.clients');

        $client = array_filter($clients, function ($client) use ($appId) {
            return $client['app_id'] == $appId;
        }, ARRAY_FILTER_USE_BOTH);

        if ( ! isset($client['app_secret'])) {
            throw new InvalidAppIdException('Invalid app_id.');
        }

        Signature::validSign($client['app_secret']);

        return $next($request);
    }
}