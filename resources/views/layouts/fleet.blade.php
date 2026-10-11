{{-- Atlas Logistics single system admin layout (fleet design system). --}}
{{-- Yields: fleet_title, fleet_subtitle, fleet_topbar_actions, content, styles, scripts. --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('fleet_title', 'Dashboard') | Atlas Logistics</title>
<link rel="stylesheet" href="{{ asset('assets/fleet/vendor/bootstrap-icons.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/fleet/fleet.css') }}">
@yield('styles')
</head>
<body>
<div class="fleet-dashboard">
<div class="fleet-shell">
<aside class="fleet-sidebar">
<div class="fleet-brand">
<div class="fleet-brand-mark"><i class="bi bi-truck-front-fill"></i></div>
<div class="fleet-brand-copy"><strong>Atlas Logistics</strong><small>SADC Cross-Border Transport</small></div>
</div>
@foreach(config('fleet-nav.sections', []) as $section)
@php
$vis = collect($section['items'] ?? [])->filter(function ($i) { return auth()->check() && auth()->user()->can($i['permission'] ?? ''); });
@endphp
@if($vis->isNotEmpty())
<div class="fleet-nav-label">{{ $section['label'] }}</div>
@foreach($vis as $item)
<a class="fleet-nav {{ request()->routeIs($item['active'] ?? '') ? 'active' : '' }}" href="{{ Route::has($item['route']) ? route($item['route'], $item['params'] ?? []) : '#' }}"><i class="bi {{ $item['icon'] }}"></i><span>{{ $item['label'] }}</span>@if(!empty($item['children']))<i class="bi bi-chevron-right nav-chevron"></i>@endif</a>
@foreach($item['children'] ?? [] as $child)
@can($child['permission'] ?? '')
<a class="fleet-nav fleet-nav-child {{ request()->routeIs($child['active'] ?? '') ? 'active' : '' }}" href="{{ Route::has($child['route']) ? route($child['route'], $child['params'] ?? []) : '#' }}"><i class="bi {{ $child['icon'] }}"></i><span>{{ $child['label'] }}</span></a>
@endcan
@endforeach
@endforeach
@endif
@endforeach
<div class="fleet-sidebar-footer"><div>Malawi &middot; SADC Region</div><div class="fleet-region">Moving Africa Forward</div></div>
</aside>
<main class="fleet-main">
<header class="fleet-topbar">
<div class="fleet-heading">
<button class="fleet-menu-button" type="button" aria-label="Toggle navigation"><i class="bi bi-list"></i></button>
<div><h1>@yield('fleet_title', 'Dashboard')</h1><p>@yield('fleet_subtitle', 'Atlas Logistics operations')</p></div>
</div>
<div class="fleet-top-actions">
@yield('fleet_topbar_actions')
@php
$fleetAlertCount = null;
try { if (auth()->check() && auth()->user()->can('LogisticsReports list')) { $d = app(\App\Services\FleetDashboardService::class)->data('month'); $fleetAlertCount = (int)($d['expiring_documents'] ?? 0) + (int)($d['services_due'] ?? 0) + (int)($d['pending_expense_approvals'] ?? 0) + (int)($d['missing_pods'] ?? 0); } } catch (\Throwable $e) { $fleetAlertCount = null; }
$fu = auth()->user(); $fn = $fu ? $fu->name : 'Administrator';
$fr = ($fu && method_exists($fu, 'getRoleNames')) ? ($fu->getRoleNames()->first() ?: 'Administrator') : 'Administrator';
$fp = preg_split('/\s+/', trim($fn)); $fi = strtoupper(substr($fp[0] ?? 'A', 0, 1) . substr($fp[1] ?? '', 0, 1));
@endphp
<a class="fleet-notification" href="{{ Route::has('logistics.compliance') ? route('logistics.compliance') : '#' }}" aria-label="Notifications"><i class="bi bi-bell"></i>@if($fleetAlertCount !== null && $fleetAlertCount > 0)<span>{{ $fleetAlertCount > 99 ? '99+' : $fleetAlertCount }}</span>@endif</a>
<div class="fleet-user">
<div class="fleet-avatar">{{ $fi }}</div>
<div class="fleet-user-copy"><strong>{{ $fn }}</strong><small>{{ $fr }}</small></div>
<form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="fleet-logout" aria-label="Log out" title="Log out"><i class="bi bi-box-arrow-right small"></i></button></form>
</div>
</div>
</header>
<div class="fleet-content">
@if(session('success') || session('status'))<x-fleet.alert type="success">{{ session('success') ?? session('status') }}</x-fleet.alert>@endif
@if(session('error'))<x-fleet.alert type="danger">{{ session('error') }}</x-fleet.alert>@endif
@if(session('warning'))<x-fleet.alert type="warning">{{ session('warning') }}</x-fleet.alert>@endif
@if(session('info'))<x-fleet.alert type="info">{{ session('info') }}</x-fleet.alert>@endif
@yield('content')
</div>
</main>
</div>
</div>
<script src="{{ asset('assets/fleet/vendor/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/fleet/fleet.js') }}"></script>
@yield('scripts')
</body>
</html>
