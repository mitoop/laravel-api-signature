<?php

namespace Mitoop\ApiSignature;

use InvalidArgumentException;

class ClientManager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved clients.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * Create a new Client manager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     *
     * @param  \GuzzleHttp\Client                           $client
     */
    public function __construct($app, $client)
    {
        $this->app = $app;
        $this->httpClient = $client;
    }

    public function connect($client = null)
    {
        $client = $client ?: $this->getDefaultClient();

        return $this->connections[$client] = $this->get($client);
    }

    protected function getDefaultClient()
    {
        return $this->app['config']['api-signature.default'];
    }

    protected function get($client)
    {
        $clientInstance = $this->connections[$client] ?? $this->resolve($client);

        return $clientInstance->setHttpClient(clone $this->httpClient);
    }

    protected function getConfig($client)
    {
        $config = $this->app['config']["api-signature.clients.{$client}"];

        if (is_null($config)) {
            throw new InvalidArgumentException("Client [{$client}] is not defined.");
        }

        $config = \array_merge($this->getDefaultConfig(), $config);

        if ($config['app_id'] == '') {
            throw new InvalidArgumentException('app_id is not defined.');
        }

        if ($config['app_secret'] == '') {
            throw new InvalidArgumentException('app_secret is not defined.');
        }

        if ($config['host'] == '') {
            throw new InvalidArgumentException('host is not defined.');
        }

        return $config;
    }

    protected function getIdentity()
    {
        return $this->app['config']['api-signature.identity'];
    }

    protected function getDefaultConfig()
    {
        return [
            'app_id'         => '',
            'app_secret'     => '',
            'scheme'         => '',
            'host'           => '',
            'ip'             => '',
            'port'           => '',
            'https_cert_pem' => false,
        ];
    }

    protected function resolve($client)
    {
        $config = $this->getConfig($client);
        $client = new Client($config['app_id'], $config['app_secret']);

        if ($identity = $this->getIdentity()) {
            $client->setIdentity($identity);
        }

        $client->setScheme($config['scheme']);
        $client->setHost($config['host']);
        $client->setIp($config['ip']);
        $client->setPort($config['port']);
        $client->setCertPem($config['https_cert_pem']);
        $client->setContainer($this->app);

        if ($loggerHandler = $this->app['config']['api-signature.logger_handler']) {
            $client->setLoggerHandler(new $loggerHandler);
        }

        return $client;
    }

    /**
     * Dynamically call the default client instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->connect()->$method(...$parameters);
    }
}
