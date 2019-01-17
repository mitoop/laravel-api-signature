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
        $this->app        = $app;
        $this->httpClient = $client;
    }

    /**
     * 生成client连接.
     *
     * @param null $client
     *
     * @return Client
     */
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
        if (isset($this->connections[$client])) {
            return $this->connections[$client]->setHttpClient($this->httpClient);
        }

        return $this->resolve($client);
    }

    /**
     * 获取客户端的配置.
     *
     * @param string $client
     *
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getConfig($client)
    {
        $config = $this->app['config']["api-signature.clients.{$client}"];

        if (is_null($config)) {
            throw new InvalidArgumentException("Client [{$client}] is not defined.");
        }

        $config = \array_merge($this->getDefaultConfig(), $config);

        if ($config['app_id'] == '') {
            throw new InvalidArgumentException("app_id is not defined.");
        }

        if ($config['app_secret'] == '') {
            throw new InvalidArgumentException("app_secret is not defined.");
        }

        if ($config['host'] == '') {
            throw new InvalidArgumentException("host is not defined.");
        }

        return $config;
    }

    /**
     * 请求发起方的身份标识.
     * @return mixed
     */
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

    /**
     * 获取Client实例.
     *
     * @param $client string client标识
     *
     * @return Client
     * @throws InvalidArgumentException
     */
    protected function resolve($client)
    {
        $config = $this->getConfig($client);
        $client = new Client($config['app_id'], $config['app_secret'], clone $this->httpClient);

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
            $client->setLoggerHandler($loggerHandler);
        }

        return $client;
    }
}