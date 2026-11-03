<?php

return [
    // All trip costs/revenue are converted into this currency for reporting.
    'base_currency' => env('LOGISTICS_BASE_CURRENCY', 'MWK'),

    // Default number of days before expiry that a document starts showing as "due soon".
    'default_alert_days' => 30,

    'trip_statuses' => [
        'planned'    => 'Planned',
        'in_transit' => 'In Transit',
        'completed'  => 'Delivered',
        'closed'     => 'Closed',
        'cancelled'  => 'Cancelled',
    ],

    'trip_types' => [
        'export'   => 'Export (out of Malawi)',
        'import'   => 'Import (into Malawi)',
        'backhaul' => 'Back-haul / return load',
        'domestic' => 'Domestic (within Malawi)',
        'transit'  => 'Transit (between other countries)',
    ],

    'payment_methods' => [
        'cash'         => 'Cash',
        'mobile_money' => 'Mobile money',
        'bank'         => 'Bank transfer',
    ],

    // Drivers report one checkpoint at departure, every stop, and arrival.
    'checkpoint_kinds' => [
        'departure' => 'Departure',
        'stop'      => 'Stop',
        'arrival'   => 'Arrival',
    ],

    // Fixed gauge scale - free text is deliberately not allowed.
    'fuel_levels' => [
        'empty'         => 'Empty',
        'quarter'       => '1/4',
        'half'          => '1/2',
        'three_quarter' => '3/4',
        'full'          => 'Full',
    ],
];
