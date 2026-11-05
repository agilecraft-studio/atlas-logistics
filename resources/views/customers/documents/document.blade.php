@php
    $meta = $document->meta ?: [];
    $code = $document->currency_code ?: \App\Services\CurrencyService::base();
    $money = fn ($amount) => \App\Support\Money::format(\App\Support\Money::toMinor($amount), $code);
    $address = array_filter([
        $company['address1'] ?? null,
        $company['address2'] ?? null,
        trim(implode(', ', array_filter([$company['city'] ?? null, $company['state'] ?? null, $company['country'] ?? null]))),
    ]);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $document->document_number }}</title>
    <style>
        @page { margin: 28px 32px 36px; }
        * { box-sizing: border-box; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; color: #1f2937; margin: 0; }
        .sheet { max-width: 760px; margin: 0 auto; padding: 24px; }
        .header { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header td { vertical-align: top; }
        .company-name { font-size: 18px; font-weight: bold; color: #111827; margin-bottom: 4px; }
        .muted { color: #6b7280; }
        .small { font-size: 10px; }
        .brand { text-align: right; }
        .brand img { max-height: 56px; max-width: 180px; }
        .title { font-size: 20px; font-weight: bold; letter-spacing: .8px; color: #0f172a; }
        .status { display: inline-block; padding: 3px 10px; border-radius: 10px; background: #e5e7eb; color: #111827; font-size: 10px; text-transform: uppercase; letter-spacing: .6px; }
        .box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 12px; }
        .label { font-size: 9px; text-transform: uppercase; letter-spacing: .8px; color: #6b7280; margin-bottom: 4px; }
        .grid { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .grid td { width: 50%; vertical-align: top; padding: 0 8px 10px 0; }
        table.facts { width: 100%; border-collapse: collapse; margin: 16px 0; }
        table.facts th { background: #f3f4f6; color: #374151; text-align: left; padding: 8px; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid #d1d5db; }
        table.facts td { padding: 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
        .notes { margin-top: 16px; }
        .notes .box { white-space: pre-line; margin-bottom: 10px; }
        .signature { margin-top: 26px; width: 100%; border-collapse: collapse; }
        .signature td { width: 50%; padding-right: 18px; vertical-align: bottom; }
        .sign-line { border-top: 1px solid #9ca3af; margin-top: 34px; padding-top: 5px; color: #6b7280; font-size: 9px; }
        .pod-image { max-width: 100%; max-height: 520px; border: 1px solid #e5e7eb; border-radius: 6px; margin-top: 12px; }
        .footer { margin-top: 26px; border-top: 1px solid #e5e7eb; padding-top: 10px; color: #6b7280; font-size: 9px; }
        .no-print { background: #111827; color: #fff; padding: 10px 16px; text-align: center; }
        .no-print button, .no-print a { padding: 7px 14px; border-radius: 4px; border: 1px solid #fff; background: transparent; color: #fff; font-size: 12px; cursor: pointer; text-decoration: none; margin-left: 8px; }
        @media print { .no-print { display: none; } body { font-size: 10.5px; } .sheet { padding: 0; max-width: none; } }
    </style>
</head>
<body>
@if($print ?? false)
<div class="no-print">
    <strong>{{ $document->document_number }}</strong>
    <button type="button" onclick="window.print()">Print</button>
    <a href="{{ route('customers.documents.download', [$document->customer_id, $document]) }}">Download PDF</a>
</div>
@endif

<div class="sheet">
    <table class="header">
        <tr>
            <td style="width:60%">
                <div class="company-name">{{ $company['name'] }}</div>
                @foreach($address as $line)
                    @if($line)<div class="small muted">{{ $line }}</div>@endif
                @endforeach
                @if($company['email'])<div class="small muted">{{ $company['email'] }}</div>@endif
                @if($company['phone'])<div class="small muted">{{ $company['phone'] }}</div>@endif
                @if($company['tax_no'])<div class="small muted">Tax no: {{ $company['tax_no'] }}</div>@endif
            </td>
            <td class="brand">
                @if($company['logo'])
                    <img src="{{ $company['logo'] }}" alt="{{ $company['name'] }}">
                @endif
                <div class="title" style="margin-top:8px">{{ $document->title }}</div>
                <div class="muted small">{{ $document->document_number }}</div>
                <div style="margin-top:6px"><span class="status">{{ $document->status_label }}</span></div>
            </td>
        </tr>
    </table>

    <table class="grid">
        <tr>
            <td>
                <div class="box">
                    <div class="label">Customer</div>
                    <div style="font-weight:bold;font-size:12px">{{ $meta['customer_name'] ?? $document->customer->name }}</div>
                    @if(!empty($meta['customer_address']))<div class="muted">{{ $meta['customer_address'] }}</div>@endif
                    @if(!empty($meta['customer_email']))<div class="muted">{{ $meta['customer_email'] }}</div>@endif
                    @if(!empty($meta['customer_phone']))<div class="muted">{{ $meta['customer_phone'] }}</div>@endif
                </div>
            </td>
            <td>
                <div class="box">
                    <table style="width:100%;border-collapse:collapse">
                        <tr><td class="muted">Issue date</td><td class="right">{{ optional($document->issue_date)->format('d M Y') ?: '—' }}</td></tr>
                        @if($document->valid_until)
                        <tr><td class="muted">Valid until</td><td class="right">{{ $document->valid_until->format('d M Y') }}</td></tr>
                        @endif
                        @if($document->amount !== null)
                        <tr><td class="muted">Quoted amount</td><td class="right" style="font-weight:bold">{{ $money($document->amount) }}</td></tr>
                        @endif
                        <tr><td class="muted">Shipment</td><td class="right">{{ $meta['trip_number'] ?? '—' }}</td></tr>
                        <tr><td class="muted">Route</td><td class="right">{{ $meta['route'] ?? '—' }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="facts">
        <thead>
            <tr>
                <th>Shipment details</th>
                <th>Driver &amp; vehicle</th>
                <th>Cargo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div><span class="muted">From:</span> {{ $meta['origin'] ?: '—' }}</div>
                    <div><span class="muted">To:</span> {{ $meta['destination'] ?: '—' }}</div>
                    <div><span class="muted">Departure:</span> {{ $meta['planned_start_at'] ? \Illuminate\Support\Carbon::parse($meta['planned_start_at'])->format('d M Y H:i') : '—' }}</div>
                    <div><span class="muted">Delivered:</span> {{ $meta['actual_end_at'] ? \Illuminate\Support\Carbon::parse($meta['actual_end_at'])->format('d M Y H:i') : '—' }}</div>
                </td>
                <td>
                    <div><span class="muted">Driver:</span> {{ $meta['driver_name'] ?: '—' }}</div>
                    <div><span class="muted">Truck:</span> {{ $meta['vehicle_plate'] ?: '—' }}</div>
                    <div><span class="muted">Trailer:</span> {{ $meta['trailer_plate'] ?: '—' }}</div>
                </td>
                <td>
                    <div>{{ $meta['cargo_description'] ?: '—' }}</div>
                    @if(!empty($meta['cargo_weight_kg']))<div><span class="muted">Weight:</span> {{ $meta['cargo_weight_kg'] }} kg</div>@endif
                    <div><span class="muted">Consignee:</span> {{ $meta['consignee_name'] ?: '—' }}</div>
                    @if(!empty($meta['consignee_phone']))<div><span class="muted">Phone:</span> {{ $meta['consignee_phone'] }}</div>@endif
                </td>
            </tr>
        </tbody>
    </table>

    @if($document->notes)
    <div class="notes">
        <div class="label">Notes</div>
        <div class="box">{{ $document->notes }}</div>
    </div>
    @endif

    @if($document->type === 'pod')
    <div class="signature">
        <table style="width:100%;border-collapse:collapse">
            <tr>
                <td>
                    <div class="label">Received by</div>
                    <div>{{ $document->signed_by ?: '—' }}</div>
                    <div class="sign-line">Name &amp; signature</div>
                </td>
                <td>
                    <div class="label">Date &amp; time</div>
                    <div>{{ optional($document->signed_at)->format('d M Y H:i') ?: optional($document->issue_date)->format('d M Y') }}</div>
                    <div class="sign-line">Signed on delivery</div>
                </td>
            </tr>
        </table>
    </div>
    @if($imageData)
    <img src="{{ $imageData }}" alt="Proof of delivery" class="pod-image">
    @endif
    @else
    <div class="signature">
        <table style="width:100%;border-collapse:collapse">
            <tr>
                <td><div class="sign-line">Driver signature</div></td>
                <td><div class="sign-line">Customer / receiver signature</div></td>
            </tr>
        </table>
    </div>
    @endif

    <div class="footer">
        {{ $company['name'] }} · {{ $document->document_number }} · {{ $document->type_label }}
        <span style="float:right">Generated {{ now()->format('d M Y H:i') }}</span>
    </div>
</div>
</body>
</html>
