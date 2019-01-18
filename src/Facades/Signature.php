<?php

namespace Mitoop\ApiSignature\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool validSign($secret)
 * @method static string sign(array $params, $secret)
 */
class Signature extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Mitoop\ApiSignature\Signature::class;
    }
}
