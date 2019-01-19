<?php

namespace Mitoop\ApiSignature;

class Client
{
    const SCHEME_HTTP = 'http';
    const SCHEME_HTTPS = 'https';

    const HTTP_DEFAULT_PORT = 80;
    const HTTPS_DEFAULT_PORT = 443;

    protected $params = [];

    protected $appId;

    protected $appSecret;

    protected $identity;

    protected $host;

    protected $ip;

    protected $scheme;

    protected $port;

    protected $method;

    protected $path;

    protected $loggerHandler;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    protected $certPem;

    protected $container;

    public function __construct($appId, $appSecret)
    {
        $this->setAppId($appId);
        $this->setAppSecret($appSecret);
    }

    protected function setMethod($method)
    {
        $this->method = \strtoupper($method);

        return $this;
    }

    protected function getMethod()
    {
        return $this->method;
    }

    protected function setPath($path)
    {
        $path = \rtrim($path, '/');
        if (\strpos($path, '/') !== 0) {
            $path = '/'.$path;
        }

        $this->path = $path;

        return $this;
    }

    protected function getPath()
    {
        return $this->path;
    }

    public function setHost($host)
    {
        $host = \ltrim($host, 'http://');
        $host = \ltrim($host, 'https://');
        $host = \rtrim($host, '/');

        $this->host = $host;

        return $this;
    }

    protected function getHost()
    {
        return $this->host;
    }

    public function setIp($ip)
    {
        if ($ip) {
            $ip = \ltrim($ip, 'http://');
            $ip = \ltrim($ip, 'https://');
            $ip = \rtrim($ip, '/');

            $this->ip = $ip;
        }

        return $this;
    }

    protected function getIp()
    {
        return $this->ip;
    }

    public function setScheme($scheme)
    {
        if ($scheme) {
            $scheme = \strtolower($scheme);

            if (! in_array($scheme, [self::SCHEME_HTTP, self::SCHEME_HTTPS])) {
                throw new \InvalidArgumentException('The supported schemes are : http and https');
            }
            $this->scheme = $scheme;
        }

        return $this;
    }

    protected function getScheme()
    {
        return $this->scheme ?: 'http';
    }

    public function setPort($port)
    {
        if ($port) {
            $this->port = \intval($port);
        }

        return $this;
    }

    protected function getPort()
    {
        if ($this->port) {
            return $this->port;
        }

        if ($this->scheme == self::SCHEME_HTTPS) {
            return self::HTTPS_DEFAULT_PORT;
        }

        return self::HTTP_DEFAULT_PORT;
    }

    protected function setDatas($key, $value = null)
    {
        if (is_array($key)) {
            return $this->setArrayDatas($key);
        }

        $this->params[$key] = $value;

        return $this;
    }

    protected function getDatas()
    {
        return $this->params;
    }

    protected function setArrayDatas(array $params)
    {
        foreach ($params as $key => $value) {
            $this->params[$key] = $value;
        }

        return $this;
    }

    public function setAppId($appId)
    {
        $this->appId = $appId;

        return $this;
    }

    protected function getAppId()
    {
        return $this->appId;
    }

    public function setAppSecret($appSecret)
    {
        $this->appSecret = $appSecret;

        return $this;
    }

    protected function getAppSecret()
    {
        return $this->appSecret;
    }

    public function setIdentity($identity)
    {
        $this->identity = $identity;

        return $this;
    }

    protected function getIdentity()
    {
        return $this->identity;
    }

    public function setLoggerHandler(SignatureLoggerInterface $handler)
    {
        $this->loggerHandler = $handler;

        return $this;
    }

    /**
     * @return SignatureLoggerInterface
     */
    protected function getLoggerHandler()
    {
        return $this->loggerHandler;
    }

    protected function resolveLog(string $message, array $data)
    {
        if ($loggerHandler = $this->getLoggerHandler()) {
            $loggerHandler->handle($message, $data);
        }
    }

    public function setCertPem($certPem)
    {
        $this->certPem = $certPem;

        return $this;
    }

    protected function getCertPem()
    {
        return $this->certPem;
    }

    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * 获取容器.
     * @return \Illuminate\Contracts\Foundation\Application
     */
    protected function getContainer()
    {
        return $this->container;
    }

    public function setHttpClient($client)
    {
        $this->httpClient = $client;

        return $this;
    }

    public function get($path, array $data = null)
    {
        $this->clearDatas();

        if ($data !== null) {
            $this->setDatas($data);
        }

        $this->setMethod('GET');

        return $this->request($path);
    }

    public function post($path, array $data = null)
    {
        $this->clearDatas();

        if ($data !== null) {
            $this->setDatas($data);
        }

        $this->setMethod('POST');

        return $this->request($path);
    }

    protected function request($path)
    {
        $requestStart = \time();

        $url = $this->getUrl($path);

        $method = $this->getMethod();

        $data = [
            'http_errors' => false
        ];

        if ($method == 'POST') {
            $data['form_params'] = $this->getDatas();
        }

        if ($ip = $this->getIp()) {
            $data['headers'] = [
                'Host' => $this->getHost(),
            ];
        }

        if ($this->getScheme() == self::SCHEME_HTTPS) {
            $data['verify'] = $this->getCertPem();
        }

        $this->resolveLog('API Data', ['method' => $this->getMethod(), 'data' => $data, 'url' => $url]);

        $response = $this->httpClient->request($method, $url, $data);

        $requestEnd = \time();
        $this->resolveLog('API End', [
            'status' => $response->getStatusCode(),
            'contents' => $response->getBody()->getContents(),
            'request_start' => $requestStart,
            'request_end' => $requestEnd,
            'time' => ($requestEnd-$requestStart).'s',
        ]);

        return new SignatureResponse($response);
    }

    protected function generateSignData()
    {
        $signData = [];
        $signData['app_id'] = $this->getAppId();
        $signData['timestamp'] = time();
        $nonce = $this->getNonce();
        $signData['nonce'] = $nonce;
        $signature = $this->getContainer()->make(Signature::class);
        $signData['sign'] = $signature->sign(\array_merge($signData, [
            'http_method' => $this->getMethod(),
            'http_path'   => $this->getPath(),
        ]), $this->getAppSecret());

        if ($this->getMethod() == 'GET') {
            if (\count(array_diff($signData, $this->getDatas())) != count($signData)) {
                throw new \InvalidArgumentException('Arguments conflicts');
            }
            $signData = array_merge($signData, $this->getDatas());
        }

        $signData = http_build_query($signData, null, '&');

        $this->resolveLog('API Start', ['nonce' => $nonce]);

        return $signData;
    }

    protected function getUrl($path)
    {
        $this->setPath($path);

        $scheme = $this->getScheme();
        $url = $scheme.'://';

        if ($ip = $this->getIp()) {
            $url .= $ip;
        } else {
            $url .= $this->getHost();
        }

        $port = $this->getPort();
        if (($scheme == 'http' && $port != self::HTTP_DEFAULT_PORT) || ($scheme == 'https' && $port != self::HTTPS_DEFAULT_PORT)) {
            $url .= ':'.$port;
        }

        return $url.$this->getPath().'?'.$this->generateSignData();
    }

    protected function getNonce()
    {
        $identity = $this->getIdentity();
        if ($this->getContainer()->version() >= '5.6.0') {
            return $identity.':'.\Illuminate\Support\Str::orderedUuid()->toString();
        }

        $hash = \md5(\uniqid($identity, true).'-'.\random_int(1, 65535).'-'.\random_int(1, 65535));

        return $identity.':'.substr($hash, 0, 8).
                             '-'.
                             \substr($hash, 8, 4).
                             '-'.
                             \substr($hash, 12, 4).
                             '-'.
                             \substr($hash, 16, 4).
                             '-'.
                             \substr($hash, 20, 12);
    }

    protected function clearDatas()
    {
        $this->params = [];
    }
}
