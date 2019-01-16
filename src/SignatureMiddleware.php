<?php

namespace Mitoop\ApiSignature;

use Illuminate\Contracts\Foundation\Application;
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
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     * @throws InvalidSignatureException
     */
    public function handle($request, \Closure $next)
    {
        Signature::validSign($request);

        return $next($request);
    }
}