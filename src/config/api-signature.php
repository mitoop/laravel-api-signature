<?php

return [
    'default' => 'client1',

    'clients' => [
        'client1' => [
            'app_id'     => 'app id',
            'app_secret' => 'app secret',
            'scheme'     => '',
            'host'       => '',
            'ip'         => '',
            'port'       => '',
        ],
    ],

    'identity'       => '',
    'logger_handler' => function (array $data) {
        \Log::info('API_CLIENT_LOG', $data);
    },
];