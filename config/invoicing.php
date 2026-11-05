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

    // Document kinds shown in the customer hub and the invoice register. Only tax invoices
    // count as revenue and accept payments; proformas and credit notes are documents.
    'document_types' => [
        'tax_invoice' => [
            'label'  => 'Tax invoice',
            'title'  => 'INVOICE',
            'prefix' => env('INVOICE_PREFIX', 'INV'),
            'icon'   => 'bi-receipt',
        ],
        'proforma' => [
            'label'  => 'Proforma invoice',
            'title'  => 'PROFORMA INVOICE',
            'prefix' => 'PRO',
            'icon'   => 'bi-file-earmark-text',
        ],
        'credit_note' => [
            'label'  => 'Credit note',
            'title'  => 'CREDIT NOTE',
            'prefix' => 'CN',
            'icon'   => 'bi-file-earmark-minus',
        ],
    ],

    'line_types' => [
        'freight'    => 'Freight',
        'extra_drop' => 'Extra drop',
        'demurrage'  => 'Demurrage',
        'border'     => 'Border / other charges',
        'other'      => 'Other charge',
    ],
];
