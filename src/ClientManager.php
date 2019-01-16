<?php

namespace Mitoop\ApiSignature;


use InvalidArgumentException;

class ClientManager
{

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved clients.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * Create a new Client manager instance.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @param null $client
     *
     * @return Client
     */
    public function connection($client = null)
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
        return $this->connections[$client] ?? $this->resolve($client);
    }

    protected function getConfig($client)
    {
        return $this->app['config']["api-signature.clients.{$client}"];
    }

    protected function getIdentity()
    {
        return $this->app['config']['api-signature.identity'];
    }

    protected function getDefaultConfig()
    {
        return [
            'app_id'     => '',
            'app_secret' => '',
            'scheme'     => '',
            'host'       => '',
            'ip'         => '',
            'port'       => '',
        ];
    }

    protected function resolve($client)
    {
        $config = $this->getConfig($client);

        if (is_null($config)) {
            throw new InvalidArgumentException("Client [{$client}] is not defined.");
        }

        $defaultConfig = $this->getDefaultConfig();

        $config = \array_merge($defaultConfig, $config);


        if ($config['app_id'] == '') {
            throw new InvalidArgumentException("app_id is not defined.");
        }

        if ($config['app_secret'] == '') {
            throw new InvalidArgumentException("app_secret is not defined.");
        }

        if ($config['host'] == '') {
            throw new InvalidArgumentException("host is not defined.");
        }

        $client = new Client($config['app_id'], $config['app_secret']);

        if ($identity = $this->getIdentity()) {
            $client->setIdentity($identity);
        }

        $client->setScheme($config['scheme']);
        $client->setHost($config['host']);
        $client->setIp($config['ip']);
        $client->setPort($config['port']);

        if ($loggerHandler = $this->app['config']['api-signature.logger_handler']) {
            $client->setLoggerHandler($loggerHandler);
        }
        
        return $client;
    }
}