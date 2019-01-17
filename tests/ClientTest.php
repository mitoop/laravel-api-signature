<?php

namespace Tests;

use Mitoop\ApiSignature\ClientManager;

class ClientTest extends TestCase
{
    public function testSign()
    {
        $response = $this->app->make(ClientManager::class)->connection($this->testingClient)->post('/', ['foo']);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
