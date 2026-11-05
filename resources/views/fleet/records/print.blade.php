<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #1f2937; font-size: 12px; margin: 0; padding: 28px; }
        .sheet { max-width: 800px; margin: 0 auto; }
        .head { display: flex; justify-content: space-between; gap: 24px; border-bottom: 3px solid #0f2a44; padding-bottom: 14px; margin-bottom: 20px; }
        .brand { font-size: 20px; font-weight: 700; color: #0f2a44; }
        .muted { color: #6b7280; }
        .title { font-size: 18px; font-weight: 700; margin: 0 0 4px; }
        .subtitle { color: #6b7280; margin: 0; }
        table.fields { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.fields td { border-bottom: 1px solid #e5e7eb; padding: 8px 6px; vertical-align: top; }
        table.fields td:first-child { width: 34%; color: #6b7280; text-transform: uppercase; font-size: 10px; letter-spacing: .04em; }
        table.lines { width: 100%; border-collapse: collapse; margin-top: 18px; }
        table.lines th, table.lines td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        table.lines th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; }
        .notes { margin-top: 20px; white-space: pre-line; line-height: 1.55; }
        .foot { margin-top: 36px; border-top: 1px solid #e5e7eb; padding-top: 10px; color: #9ca3af; font-size: 10px; display: flex; justify-content: space-between; }
        .toolbar { max-width: 800px; margin: 0 auto 18px; display: flex; gap: 8px; justify-content: flex-end; }
        .btn { border: 1px solid #0f2a44; background: #0f2a44; color: #fff; padding: 7px 14px; border-radius: 6px; font-size: 12px; cursor: pointer; text-decoration: none; }
        .btn.light { background: #fff; color: #0f2a44; }
        @media print { .toolbar { display: none; } body { padding: 0; } .sheet { max-width: none; } }
    </style>
</head>
<body>
@if($print)
<div class="toolbar">
    <a href="{{ url()->previous() }}" class="btn light">Back</a>
    <button class="btn" onclick="window.print()">Print</button>
</div>
@endif

<div class="sheet">
    <div class="head">
        <div>
            <div class="brand">{{ $company['name'] }}</div>
            <div class="muted">{{ $company['address'] }}</div>
            <div class="muted">
                {{ $company['phone'] }}
                @if($company['phone'] && $company['email']) · @endif
                {{ $company['email'] }}
            </div>
        </div>
        <div style="text-align:right">
            <div class="muted">Generated {{ now()->format('d M Y H:i') }}</div>
            <div class="muted">Atlas Fleet Management</div>
        </div>
    </div>

    <h1 class="title">{{ $title }}</h1>
    @if($subtitle)<p class="subtitle">{{ $subtitle }}</p>@endif

    <table class="fields">
        @foreach($fields as $label => $value)
        <tr><td>{{ $label }}</td><td>{{ $value !== '' ? $value : '—' }}</td></tr>
        @endforeach
    </table>

    @if($lines)
    <table class="lines">
        <thead><tr>@foreach($lines['headers'] as $header)<th>{{ $header }}</th>@endforeach</tr></thead>
        <tbody>
        @foreach($lines['rows'] as $row)
        <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
        @endforeach
        </tbody>
    </table>
    @endif

    @if($notes)
    <div class="notes">
        <div class="muted" style="text-transform:uppercase;font-size:10px;letter-spacing:.04em;margin-bottom:4px">Notes</div>
        {{ $notes }}
    </div>
    @endif

    <div class="foot">
        <span>{{ $company['name'] }} · Fleet operations</span>
        <span>{{ $title }}</span>
    </div>
</div>
</body>
</html>
