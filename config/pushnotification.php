<?php
/**
 * @see https://github.com/Edujugon/PushNotification
 */
/*
@copyright

Fleet Manager v6.1

Copyright (C) 2017-2022 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */
return [
    'gcm' => [
        'priority' => 'normal',
        'dry_run' => false,
        'apiKey' => env('server_key'),
    ],
    'fcm' => [
        'priority' => 'normal',
        'dry_run' => false,
        'apiKey' => env('server_key'),
    ],
    'apn' => [
        'certificate' => __DIR__ . '/iosCertificates/apns-dev-cert.pem',
        'passPhrase' => 'secret', //Optional
        'passFile' => __DIR__ . '/iosCertificates/yourKey.pem', //Optional
        'dry_run' => true,
    ],
];
