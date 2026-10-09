<?php
/*
@copyright

Fleet Manager v6.1

Copyright (C) 2017-2022 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */
return [

    'requirements' => [
        'openssl',
        'pdo',
        'mbstring',
        'tokenizer',
    ],

    'permissions' => [
        'storage/app/' => '775',
        'storage/framework/' => '775',
        'storage/logs/' => '775',
        'bootstrap/cache/' => '775',
    ],
];
