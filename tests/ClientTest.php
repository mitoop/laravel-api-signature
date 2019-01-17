<?php

namespace Tests;

use Mitoop\ApiSignature\ClientManager;

class ClientTest extends TestCase
{
    public function testPost()
    {
        // 200 success
        $response = $this->app
            ->make(ClientManager::class)
            ->connection($this->testingClient)
            ->post('/', ['foo' => 'bar']);
        $this->assertTrue($response->isOk());

        // 400 ClientError
        $response = $this->app
            ->make(ClientManager::class)
            ->connection($this->testingClient)
            ->post('/', ['foo' => 'bar']);
        $this->assertTrue($response->isClientError());

        // 500 ServerError
        $response = $this->app
            ->make(ClientManager::class)
            ->connection($this->testingClient)
            ->post('/', ['foo' => 'bar']);
        $this->assertTrue($response->isServerError());
    }

    public function testGet()
    {
        // 200 success
        $response = $this->app
            ->make(ClientManager::class)
            ->connection($this->testingClient)
            ->get('/', ['foo' => 'bar']);
        $this->assertTrue($response->isOk());

        // 400 ClientError
        $response = $this->app
            ->make(ClientManager::class)
            ->connection($this->testingClient)
            ->get('/', ['foo' => 'bar']);
        $this->assertTrue($response->isClientError());

        // 500 ServerError
        $response = $this->app
            ->make(ClientManager::class)
            ->connection($this->testingClient)
            ->get('/', ['foo' => 'bar']);
        $this->assertTrue($response->isServerError());
    }
}
