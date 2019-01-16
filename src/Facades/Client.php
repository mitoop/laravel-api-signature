<?php

namespace Mitoop\ApiSignature\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Mitoop\ApiSignature\Client  connection(string|null $client = null)
 */
class Client extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'api-client';
    }
}
