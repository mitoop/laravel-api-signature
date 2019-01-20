<?php

namespace Mitoop\ApiSignature\Facades;

use Illuminate\Support\Facades\Facade;
use Mitoop\ApiSignature\ClientManager;

/**
 * @method static \Mitoop\ApiSignature\Client  connect(string|null $client = null)
 * @method static \Mitoop\ApiSignature\SignatureResponse  get($path, array $data = null, array $headers = null)
 * @method static \Mitoop\ApiSignature\SignatureResponse  post($path, array $data = null, array $headers = null)
 * @method static \Mitoop\ApiSignature\SignatureResponse  put($path, array $data = null, array $headers = null)
 * @method static \Mitoop\ApiSignature\SignatureResponse  delete($path, array $data = null, array $headers = null)
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
