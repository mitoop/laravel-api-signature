<?php

namespace Mitoop\ApiSignature\Facades;

use Illuminate\Support\Facades\Facade;
use Mitoop\ApiSignature\ClientManager;

/**
 * @method static \Mitoop\ApiSignature\Client  connect(string|null $client = null)
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
        return ClientManager::class;
    }
}
