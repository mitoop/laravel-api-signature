<?php

return [
    'default' => 'client1',

    'clients' => [
        'client1' => [
            'app_id'         => 'app id', // app id 必填
            'app_secret'     => 'app secret', //app secret 必填
            'scheme'         => '', // https or https 非必填
            'host'           => '', // www.google.com 必填
            'ip'             => '', // 192.169.11.11 非必填
            'port'           => '', // 80 非必填
            'https_cert_pem' => 'cert.pem path', // https请求时 证书路径 true 调用系统证书 false 禁用验证
        ],

        // another client
        'client2' => [
            'app_id'         => 'app id', // app id 必填
            'app_secret'     => 'app secret', //app secret 必填
            'scheme'         => '', // https or https 非必填
            'host'           => '', // www.google.com 必填
            'ip'             => '', // 192.169.11.11 非必填
            'port'           => '', // 80 非必填
            'https_cert_pem' => 'cert.pem path', // https请求时 证书路径 true 调用系统证书 false 禁用验证
        ],
    ],

    'identity'       => '', // 标识当前系统的身份 用户生成nonce request id时作为前缀
    'logger_handler' => function ($message, array $data) {
        \Log::info($message, $data);
    },
];