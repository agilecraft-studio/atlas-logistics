<?php

return [
    // Operational documents stored against a customer. Invoices (tax, proforma, credit note)
    // live in the invoicing module and are surfaced in the same customer hub.
    'types' => [
        'delivery_note' => [
            'label'         => 'Delivery note',
            'plural'        => 'Delivery notes',
            'title'         => 'DELIVERY NOTE',
            'prefix'        => 'DN',
            'icon'          => 'bi-box-seam',
            'trip_required' => true,
            'file_required' => false,
            'statuses'      => ['draft', 'dispatched', 'delivered', 'cancelled'],
        ],
        'dispatch_sheet' => [
            'label'         => 'Dispatch sheet',
            'plural'        => 'Dispatch / route sheets',
            'title'         => 'DISPATCH / ROUTE SHEET',
            'prefix'        => 'DS',
            'icon'          => 'bi-signpost-split',
            'trip_required' => true,
            'file_required' => false,
            'statuses'      => ['draft', 'dispatched', 'delivered', 'cancelled'],
        ],
        'waybill' => [
            'label'         => 'Waybill / BoL',
            'plural'        => 'Waybills / Bills of Lading',
            'title'         => 'WAYBILL / BILL OF LADING',
            'prefix'        => 'WB',
            'icon'          => 'bi-file-earmark-ruled',
            'trip_required' => true,
            'file_required' => false,
            'statuses'      => ['draft', 'dispatched', 'in_transit', 'delivered', 'cancelled'],
        ],
        'pod' => [
            'label'         => 'Proof of delivery',
            'plural'        => 'Proofs of delivery',
            'title'         => 'PROOF OF DELIVERY',
            'prefix'        => 'POD',
            'icon'          => 'bi-patch-check',
            'trip_required' => true,
            'file_required' => true,
            'statuses'      => ['pending', 'delivered', 'rejected'],
        ],
        'quote' => [
            'label'         => 'Freight quote',
            'plural'        => 'Freight quotes',
            'title'         => 'FREIGHT QUOTE',
            'prefix'        => 'QT',
            'icon'          => 'bi-calculator',
            'trip_required' => false,
            'file_required' => false,
            'statuses'      => ['draft', 'pending', 'approved', 'rejected', 'expired'],
            'has_amount'    => true,
            'has_validity'  => true,
        ],
    ],

    'statuses' => [
        'draft'      => 'Draft',
        'pending'    => 'Pending',
        'approved'   => 'Approved',
        'rejected'   => 'Rejected',
        'dispatched' => 'Dispatched',
        'in_transit' => 'In Transit',
        'delivered'  => 'Delivered',
        'paid'       => 'Paid',
        'overdue'    => 'Overdue',
        'cancelled'  => 'Cancelled',
        'expired'    => 'Expired',
    ],

    // Sensible starting status when a document is generated from a shipment.
    'trip_status_map' => [
        'planned'    => 'draft',
        'in_transit' => 'in_transit',
        'completed'  => 'delivered',
        'closed'     => 'delivered',
        'cancelled'  => 'cancelled',
    ],
];
