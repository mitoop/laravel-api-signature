<?php

namespace Tests\Utils;

use Illuminate\Support\Collection;
use Mitoop\ApiSignature\SignatureLoggerInterface;

class TestingSignatureLogger implements SignatureLoggerInterface
{
    public function handle(string $message, array $data)
    {
        /** @var Collection $logs */
        $logs = app('log-mock');
        $logs->put($message, $data);
    }
}
