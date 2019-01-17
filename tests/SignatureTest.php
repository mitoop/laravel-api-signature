<?php

namespace Tests;

use Illuminate\Http\Request;
use Mitoop\ApiSignature\Client;
use Mitoop\ApiSignature\ClientManager;
use Mitoop\ApiSignature\Signature;

class SignatureTest extends TestCase
{

    public function testValidSign()
    {
        $this->assertTrue(false);
    }

    public function testSign()
    {
        $response = $this->app->make(ClientManager::class)->connection($this->testingClient)->post('/', ['fasfa']);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
