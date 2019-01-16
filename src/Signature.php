<?php

namespace Mitoop\ApiSignature;


use Illuminate\Support\Facades\Cache;
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

    public function sign(array $params, $secret)
    {
        $params = array_filter($params, function ($value, $key) {
            return in_array($key, $this->signKeys);
        }, ARRAY_FILTER_USE_BOTH);

        ksort($params);

        return hash_hmac('sha256', http_build_query($params, null, '&'), $secret);
    }

    public function validSign($secret)
    {
        $timestamp  = request()->query('timestamp', 0);
        $nonce      = request()->query('nonce');
        $sign       = request()->query('sign');
        $signParams = request()->query();
        $method     = request()->method(); // method() is always uppercase
        $path       = request()->getPathInfo();
        $signParams = \array_merge($signParams, [
            'http_method' => $method,
            'http_path'   => $path,
        ]);

        $this->validTimestamp($timestamp)
             ->validNonce($nonce)
             ->validHmac($secret, $signParams, $sign);

        $this->setNonceCache($nonce);

        return true;
    }

    private function validHmac($params, $secret, $hmac)
    {
        if (\is_null($hmac) || ! hash_equals($this->sign($params, $secret), $hmac)) {
            throw new InvalidSignatureException('Invalid Signature');
        }

        return $this;
    }

    private function validTimestamp($time)
    {
        $time = \intval($time);

        if ($time <= 0 || time() - $time > self::TIME_OUT) {
            throw new InvalidSignatureException('Time out.');
        }

        return $this;
    }

    private function validNonce($nonce)
    {
        if (\is_null($nonce) || ! Cache::has($nonce)) {
            throw new InvalidSignatureException('Not once');
        }

        return $this;
    }

    private function setNonceCache($nonce)
    {
        // redis driver is recommended
        Cache::add($nonce, 1, self::TIME_OUT / 60);
    }

}