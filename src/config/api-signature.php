<?php

return [
    'default' => 'main',

    'clients' => [
        'main' => [
            'app_id'         => env('SIGN_main_APP_ID', 'app id'), // app id 必填
            'app_secret'     => env('SIGN_main_APP_SECRET', 'app secret'), //app secret 必填
            'scheme'         => env('SIGN_main_SCHEME', ''), // https or https 非必填
            'host'           => env('SIGN_main_HOST', ''), // www.google.com 必填
            'ip'             => env('SIGN_main_IP', ''), // 192.169.11.11 非必填
            'port'           => env('SIGN_main_PORT', ''), // 80 非必填
            'https_cert_pem' => env('SIGN_main_HTTPS_CERT_PEM', 'cert.pem path'), // https请求时 证书路径 true 调用系统证书 false 禁用验证
        ],

        // another client
        'minor' => [
            'app_id'         => env('SIGN_MINOR_APP_ID', 'app id'), // app id 必填
            'app_secret'     => env('SIGN_MINOR_APP_SECRET', 'app secret'), //app secret 必填
            'scheme'         => env('SIGN_MINOR_SCHEME', ''), // https or https 非必填
            'host'           => env('SIGN_MINOR_HOST', ''), // www.google.com 必填
            'ip'             => env('SIGN_MINOR_IP', ''), // 192.169.11.11 非必填
            'port'           => env('SIGN_MINOR_PORT', ''), // 80 非必填
            'https_cert_pem' => env('SIGN_MINOR_HTTPS_CERT_PEM', 'cert.pem path'), // https请求时 证书路径 true 调用系统证书 false 禁用验证
        ],
    ],

    'identity'       => '', // 标识当前系统的身份 用户生成 nonce request id 时作为前缀

    'logger_handler' => \Mitoop\ApiSignature\DefaultSignatureLogger::class,
];