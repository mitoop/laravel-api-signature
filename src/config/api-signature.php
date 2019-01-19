<?php

return [
    'default' => 'client-one',

    'clients' => [
        'client-one' => [
            'app_id'         => env('SIGN_CLIENT_ONE_APP_ID', 'app id'),
            'app_secret'     => env('SIGN_CLIENT_ONE_APP_SECRET', 'app secret'),
            'scheme'         => env('SIGN_CLIENT_ONE_SCHEME', ''),
            'host'           => env('SIGN_CLIENT_ONE_HOST', ''),
            'ip'             => env('SIGN_CLIENT_ONE_IP', ''),
            'port'           => env('SIGN_CLIENT_ONE_PORT', ''),
            'https_cert_pem' => env('SIGN_CLIENT_ONE_HTTPS_CERT_PEM', 'cert.pem path'),
        ],

        'another-client' => [
            'app_id'         => env('SIGN_ANOTHER_CLIENT_APP_ID', 'app id'),
            'app_secret'     => env('SIGN_ANOTHER_CLIENT_APP_SECRET', 'app secret'),
            'scheme'         => env('SIGN_ANOTHER_CLIENT_SCHEME', ''),
            'host'           => env('SIGN_ANOTHER_CLIENT_HOST', ''),
            'ip'             => env('SIGN_ANOTHER_CLIENT_IP', ''),
            'port'           => env('SIGN_ANOTHER_CLIENT_PORT', ''),
            'https_cert_pem' => env('SIGN_ANOTHER_CLIENT_HTTPS_CERT_PEM', 'cert.pem path'),
        ],
        //... more clients
    ],

    'identity' => 'self-client',

    'logger_handler' => \Mitoop\ApiSignature\DefaultSignatureLogger::class,
];
