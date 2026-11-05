@php
    $money = fn ($amount) => \App\Support\Money::format(\App\Support\Money::toMinor($amount), $invoice->currency_code, $invoice->currency_symbol);
    $qty = fn ($value) => rtrim(rtrim((string) $value, '0'), '.') ?: '0';
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
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 28px 32px 36px; }
        * { box-sizing: border-box; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; color: #1f2937; margin: 0; }
        .sheet { max-width: 760px; margin: 0 auto; padding: 24px; }
        .header { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
        .header td { vertical-align: top; }
        .company-name { font-size: 18px; font-weight: bold; color: #111827; margin-bottom: 4px; }
        .muted { color: #6b7280; }
        .small { font-size: 10px; }
        .brand { text-align: right; }
        .brand img { max-height: 56px; max-width: 180px; }
        .title { font-size: 26px; font-weight: bold; letter-spacing: 1px; color: #0f172a; }
        .status { display: inline-block; padding: 3px 10px; border-radius: 10px; background: #e5e7eb; color: #111827; font-size: 10px; text-transform: uppercase; letter-spacing: .6px; }
        .meta { width: 100%; border-collapse: collapse; margin: 18px 0 24px; }
        .meta td { vertical-align: top; padding: 0 10px 0 0; }
        .meta .box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 12px; }
        .label { font-size: 9px; text-transform: uppercase; letter-spacing: .8px; color: #6b7280; margin-bottom: 4px; }
        table.lines { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.lines th { background: #f3f4f6; color: #374151; text-align: left; padding: 8px; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid #d1d5db; }
        table.lines td { padding: 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
        .totals { width: 46%; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 6px 8px; }
        .totals .grand td { border-top: 2px solid #111827; font-size: 13px; font-weight: bold; padding-top: 9px; }
        .totals .balance td { background: #f9fafb; font-weight: bold; }
        .notes { margin-top: 18px; }
        .notes .box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 12px; margin-bottom: 10px; white-space: pre-line; }
        .footer { margin-top: 26px; border-top: 1px solid #e5e7eb; padding-top: 10px; color: #6b7280; font-size: 9px; }
        .no-print { background: #111827; color: #fff; padding: 10px 16px; text-align: center; }
        .no-print button, .no-print a { padding: 7px 14px; border-radius: 4px; border: 1px solid #fff; background: transparent; color: #fff; font-size: 12px; cursor: pointer; text-decoration: none; margin-left: 8px; }
        @media print { .no-print { display: none; } body { font-size: 10.5px; } .sheet { padding: 0; max-width: none; } }
    </style>
</head>
<body>
@if($print ?? false)
<div class="no-print">
    <strong>{{ $invoice->invoice_number }}</strong>
    <button type="button" onclick="window.print()">Print</button>
    <a href="{{ route('logistics.invoices.pdf', $invoice) }}">Download PDF</a>
</div>
@endif

<div class="sheet">
    <table class="header">
        <tr>
            <td style="width:62%">
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
                <div class="title" style="margin-top:8px">{{ $invoice->document_title }}</div>
                <div class="muted small">{{ $invoice->invoice_number }}</div>
                <div style="margin-top:6px"><span class="status">{{ $invoice->status_label }}</span></div>
            </td>
        </tr>
    </table>

    @if($invoice->sourceInvoice)
    <div class="small muted" style="margin:-10px 0 14px">
        Corrects invoice {{ $invoice->sourceInvoice->invoice_number }}
        @if($invoice->credit_reason) · {{ $invoice->credit_reason }} @endif
    </div>
    @endif

    <table class="meta">
        <tr>
            <td style="width:40%">
                <div class="box">
                    <div class="label">Bill to</div>
                    <div style="font-weight:bold;font-size:12px">{{ $invoice->client_name }}</div>
                    @if($invoice->client_address)<div class="muted">{{ $invoice->client_address }}</div>@endif
                    @if($invoice->client_email)<div class="muted">{{ $invoice->client_email }}</div>@endif
                    @if($invoice->client_phone)<div class="muted">{{ $invoice->client_phone }}</div>@endif
                </div>
            </td>
            <td>
                <div class="box">
                    <table style="width:100%;border-collapse:collapse">
                        <tr><td class="muted">Issue date</td><td class="right">{{ optional($invoice->issue_date)->format('d M Y') ?: '—' }}</td></tr>
                        <tr><td class="muted">Due date</td><td class="right">{{ optional($invoice->due_date)->format('d M Y') ?: '—' }}</td></tr>
                        <tr><td class="muted">Currency</td><td class="right">{{ $invoice->currency_code }} ({{ $invoice->currency_symbol }})</td></tr>
                        <tr><td class="muted">Exchange rate</td><td class="right">1 {{ $invoice->currency_code }} = {{ \App\Support\Money::roundDecimal($invoice->exchange_rate, 6) }} {{ $invoice->base_currency_code }}</td></tr>
                        <tr><td class="muted">Tax rate</td><td class="right">{{ \App\Support\Money::roundDecimal($invoice->tax_rate, 4) }}%</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="lines">
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th>Description</th>
                <th style="width:8%" class="right">Qty</th>
                <th style="width:22%" class="right">Unit price</th>
                <th style="width:22%" class="right">Line total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($invoice->lines as $index => $line)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    {{ $line->description }}
                    @if($line->trip)<div class="muted small">{{ $line->trip->trip_number }}</div>@endif
                </td>
                <td class="right">{{ $qty($line->quantity) }}</td>
                <td class="right nowrap">{{ $money($line->unit_price) }}</td>
                <td class="right nowrap">{{ $money($line->line_total) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td class="muted">Subtotal</td><td class="right nowrap">{{ $money($invoice->subtotal) }}</td></tr>
        <tr><td class="muted">Tax ({{ \App\Support\Money::roundDecimal($invoice->tax_rate, 4) }}%)</td><td class="right nowrap">{{ $money($invoice->tax_amount) }}</td></tr>
        <tr class="grand"><td>Total</td><td class="right nowrap">{{ $money($invoice->total) }}</td></tr>
        <tr><td class="muted">Paid</td><td class="right nowrap">{{ $money($invoice->amount_paid) }}</td></tr>
        <tr class="balance"><td>Balance due</td><td class="right nowrap">{{ $money($invoice->balance_due) }}</td></tr>
        @if($invoice->base_currency_code !== $invoice->currency_code)
        <tr><td class="muted small">Total in {{ $invoice->base_currency_code }}</td><td class="right nowrap small">{{ \App\Support\Money::format(\App\Support\Money::toMinor($invoice->total_base), $invoice->base_currency_code) }}</td></tr>
        @endif
    </table>

    <div class="notes">
        @if($invoice->payment_instructions)
        <div class="label">Payment instructions</div>
        <div class="box">{{ $invoice->payment_instructions }}</div>
        @endif
        @if($invoice->notes)
        <div class="label">Notes</div>
        <div class="box">{{ $invoice->notes }}</div>
        @endif
        @if($invoice->payments->isNotEmpty())
        <div class="label">Payments received</div>
        <div class="box">
            @foreach($invoice->payments as $payment)
            <div>{{ optional($payment->paid_at)->format('d M Y') }} · {{ $payment->method_label }}@if($payment->reference) · {{ $payment->reference }}@endif · {{ $money($payment->amount) }}</div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="footer">
        {{ $company['name'] }} · {{ $invoice->invoice_number }} · Issued {{ optional($invoice->issued_at)->format('d M Y') ?: optional($invoice->issue_date)->format('d M Y') }}
        <span style="float:right">Generated {{ now()->format('d M Y H:i') }}</span>
    </div>
</div>
</body>
</html>
