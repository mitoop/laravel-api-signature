<?php

namespace Mitoop\ApiSignature\Facades;

use Illuminate\Support\Facades\Facade;

class Signature extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'api-signature';
    }
}
