<?php

return [
    // Invoice number format: PREFIX-YEAR-0001, sequence resets each year.
    'prefix' => env('INVOICE_PREFIX', 'INV'),

    'default_due_days' => (int) env('INVOICE_DUE_DAYS', 30),

    'statuses' => [
        'draft'     => 'Draft',
        'issued'    => 'Issued',
        'part_paid' => 'Part-paid',
        'paid'      => 'Paid',
        'cancelled' => 'Cancelled',
    ],

    'line_types' => [
        'freight'    => 'Freight',
        'extra_drop' => 'Extra drop',
        'demurrage'  => 'Demurrage',
        'border'     => 'Border / other charges',
        'other'      => 'Other charge',
    ],
];
