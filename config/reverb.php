<?php

return [
    'apps' => [
        [
            'app_id' => env('REVERB_APP_ID'),
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'allowed_origins' => ['*'],
            'ping_interval' => env('REVERB_PING_INTERVAL', 60),
            'max_message_size' => env('REVERB_MAX_MESSAGE_SIZE', 10_000),
        ],
    ],

    'scaling' => [
        'enabled' => env('REVERB_SCALING_ENABLED', false),
        'channel' => env('REVERB_SCALING_CHANNEL', 'reverb_scaling'),
    ],

    'pulse_ingest' => env('REVERB_PULSE_INGEST', false),

    'options' => [
        'host' => env('REVERB_HOST', '0.0.0.0'),
        'port' => env('REVERB_PORT', 8080),
        'hostname' => env('REVERB_HOSTNAME', null),
        'max_request_size' => env('REVERB_MAX_REQUEST_SIZE', 10_000),
    ],

    'additional_options' => [
        'ssl' => [
            'local_cert' => env('REVERB_SSL_CERT', null),
            'local_pk' => env('REVERB_SSL_KEY', null),
            'passphrase' => env('REVERB_SSL_PASSPHRASE', null),
        ],
    ],
];
