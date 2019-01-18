<?php

namespace Mitoop\ApiSignature;


class DefaultSignatureLogger implements SignatureLoggerInterface
{
    public function handle(string $message, array $data)
    {
        if(config('app.debug')) {
            \Log::info($message, $data);
        }
    }
}