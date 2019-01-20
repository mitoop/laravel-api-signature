<?php

namespace Mitoop\ApiSignature;

use Mitoop\ApiSignature\Exception\InvalidSignatureException;

class Signature
{
    const TIME_OUT = 30;

    protected $signKeys = [
        'app_id',
        'timestamp',
        'nonce',
        'http_method',
        'http_path',
    ];

    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Create a new Client manager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get sinature.
     *
     * @param array $params
     * @param       $secret
     *
     * @return string
     */
    public function sign(array $params, $secret)
    {
        $params = array_filter($params, function ($value, $key) {
            return in_array($key, $this->signKeys);
        }, ARRAY_FILTER_USE_BOTH);

        ksort($params);

        return hash_hmac('sha256', http_build_query($params, null, '&'), $secret);
    }

    /**
     * Validate signature.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     * @throws InvalidSignatureException
     */
    public function validSign(\Illuminate\Http\Request $request = null)
    {
        if (\is_null($request)) {
            $request = $this->app['request'];
        }

        $appId = $request->query('app_id');
        $secret = $this->validAppId($appId);
        $timestamp = $request->query('timestamp', 0);
        $nonce = $request->query('nonce');
        $sign = $request->query('sign');
        $signParams = $request->query();
        $signParams = \array_merge($signParams, [
            'http_method' => $request->method(), // method() is always uppercase
            'http_path'   => $request->getPathInfo(),
        ]);

        $this->validTimestamp($timestamp)
             ->validNonce($nonce)
             ->validHmac($signParams, $secret, $sign);

        $this->setNonceCache($nonce);

        return true;
    }

    /**
     * Validate app id and get app secret.
     *
     * @param $appId
     *
     * @return string
     * @throws InvalidSignatureException
     */
    private function validAppId($appId)
    {
        if (\is_null($appId)) {
            throw new InvalidSignatureException('app_id is lost.');
        }

        $clients = $this->app['config']->get('api-signature.clients', []);

        $client = \current(array_filter($clients, function ($client) use ($appId) {
            return $client['app_id'] == $appId;
        }, ARRAY_FILTER_USE_BOTH));

        if ($client === false || ! isset($client['app_secret'])) {
            throw new InvalidSignatureException('Invalid app_id.');
        }

        return $client['app_secret'];
    }

    /**
     * Validate hmac.
     *
     * @param $params array
     * @param $secret string
     * @param $hmac   string
     *
     * @return $this
     * @throws InvalidSignatureException
     */
    private function validHmac($params, $secret, $hmac)
    {
        if (\is_null($hmac) || ! hash_equals($this->sign($params, $secret), $hmac)) {
            throw new InvalidSignatureException('Invalid Signature');
        }

        return $this;
    }

    /**
     * Validate timestamp.
     *
     * @param $time
     *
     * @return $this
     * @throws InvalidSignatureException
     */
    private function validTimestamp($time)
    {
        $time = \intval($time);
        $currentTime = time();

        if ($time <= 0 || $time > $currentTime || $currentTime - $time > self::TIME_OUT) {
            throw new InvalidSignatureException('Time out.');
        }

        return $this;
    }

    /**
     * Validate nonce.
     *
     * @param $nonce
     *
     * @return $this
     * @throws InvalidSignatureException
     */
    private function validNonce($nonce)
    {
        if (\is_null($nonce) || $this->app['cache']->has($this->getNonceCacheKey($nonce))) {
            throw new InvalidSignatureException('Not once');
        }

        return $this;
    }

    /**
     * Create nonce cache.
     *
     * @param $nonce
     */
    private function setNonceCache($nonce)
    {
        $this->app['cache']->add($this->getNonceCacheKey($nonce), 1, self::TIME_OUT / 60);
    }

    private function getNonceCacheKey($nonce)
    {
        return 'api:nonce:'.$nonce;
    }
}
