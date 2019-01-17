<?php

namespace Tests;

use Mitoop\ApiSignature\ClientManager;
use Mitoop\ApiSignature\SignatureResponse;

class SignatureResponseTest extends TestCase
{
    /**
     * @var SignatureResponse
     */
    private $response200;
    /**
     * @var SignatureResponse
     */
    private $response400;
    /**
     * @var SignatureResponse
     */
    private $response500;

    protected function setUp()
    {
        parent::setUp();
        $this->response200 = $this->app
            ->make(ClientManager::class)
            ->connection($this->testingClient)
            ->post('/', ['foo' => 'bar']);
        $this->response400 = $this->app
            ->make(ClientManager::class)
            ->connection($this->testingClient)
            ->post('/', ['foo' => 'bar']);
        $this->response500 = $this->app
            ->make(ClientManager::class)
            ->connection($this->testingClient)
            ->post('/', ['foo' => 'bar']);
    }

    public function testIsOk()
    {
        $this->assertTrue($this->response200->isOk());
        $this->assertFalse($this->response400->isOk());
        $this->assertFalse($this->response500->isOk());
    }

    public function testBody()
    {
        $this->assertEquals($this->testingBody, $this->response200->body());
        $this->assertEquals('', $this->response400->body());
    }

    public function testHeaders()
    {
        $this->assertEquals($this->testingHeaders, $this->response200->headers());
    }

    public function testJson()
    {
        $this->assertEquals(json_decode($this->testingBody, true), $this->response200->json());
    }

    public function testStatus()
    {
        $this->assertEquals(200, $this->response200->status());
        $this->assertEquals(400, $this->response400->status());
        $this->assertEquals(500, $this->response500->status());
    }

    public function testIsServerError()
    {
        $this->assertTrue($this->response500->isServerError());
    }

    public function testIsSuccess()
    {
        $this->assertTrue($this->response200->isSuccess());
    }

    public function testIsClientError()
    {
        $this->assertTrue($this->response400->isClientError());
    }

    public function testHeader()
    {
        $this->assertEquals('Bar', $this->response200->header('X-Foo'));
    }
}
