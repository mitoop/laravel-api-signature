<?php

namespace Mitoop\ApiSignature;

interface SignatureLoggerInterface
{
    /**
     * 日志处理方法.
     *
     * @param  string $message
     * @param  array $data
     * @return mixed
     */
    public function handle(string $message, array $data);
}
