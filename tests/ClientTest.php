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
            ->connect($this->testingClient)
            ->post('/', ['foo' => 'bar']);
        $this->assertTrue($response->isOk());
        $this->assertEquals(3, $this->app->make('log-mock')->count());
        $this->assertArrayHasKey('API Data', $this->app->make('log-mock')->toArray());
        $this->assertArrayHasKey('API End', $this->app->make('log-mock')->toArray());
        $this->assertArrayHasKey('API Start', $this->app->make('log-mock')->toArray());

        // 400 ClientError
        $response = $this->app
            ->make(ClientManager::class)
            ->connect($this->testingClient)
            ->post('/', ['foo' => 'bar']);
        $this->assertTrue($response->isClientError());

        // 500 ServerError
        $response = $this->app
            ->make(ClientManager::class)
            ->connect($this->testingClient)
            ->post('/', ['foo' => 'bar']);
        $this->assertTrue($response->isServerError());
    }

    public function testGet()
    {
        // 200 success
        $response = $this->app
            ->make(ClientManager::class)
            ->connect($this->testingClient)
            ->get('/', ['foo' => 'bar']);
        $this->assertTrue($response->isOk());

        // 400 ClientError
        $response = $this->app
            ->make(ClientManager::class)
            ->connect($this->testingClient)
            ->get('/', ['foo' => 'bar']);
        $this->assertTrue($response->isClientError());

        // 500 ServerError
        $response = $this->app
            ->make(ClientManager::class)
            ->connect($this->testingClient)
            ->get('/', ['foo' => 'bar']);
        $this->assertTrue($response->isServerError());
    }
}
