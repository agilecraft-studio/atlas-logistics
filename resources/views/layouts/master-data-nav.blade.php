@php
    $masterGroups = [
        [
            'label' => 'Fleet Configuration',
            'items' => [
                ['label' => 'Vehicle Types', 'icon' => 'bi-truck', 'permission' => 'VehicleType list', 'url' => route('vehicle-types.index'), 'active' => Request::is('admin/vehicle-types*')],
                ['label' => 'Vehicle Groups', 'icon' => 'bi-collection', 'permission' => 'VehicleGroup list', 'url' => route('vehicle_group.index'), 'active' => Request::is('admin/vehicle_group*')],
            ],
        ],
        [
            'label' => 'Inventory & Services',
            'items' => [
                ['label' => 'Parts', 'icon' => 'bi-box-seam', 'permission' => 'Parts list', 'url' => route('parts.index'), 'active' => Request::is('admin/parts') || Request::is('admin/parts/*')],
                ['label' => 'Parts Categories', 'icon' => 'bi-tags', 'permission' => 'PartsCategory list', 'url' => route('parts-category.index'), 'active' => Request::is('admin/parts-category*')],
                ['label' => 'Service Items', 'icon' => 'bi-wrench-adjustable', 'permission' => 'ServiceItems list', 'url' => route('service-item.index'), 'active' => Request::is('admin/service-item*')],
            ],
        ],
        [
            'label' => 'Partners & Resources',
            'items' => [
                ['label' => 'Mechanics', 'icon' => 'bi-person-gear', 'permission' => 'Mechanics list', 'url' => route('mechanic.index'), 'active' => Request::is('admin/mechanic*')],
                ['label' => 'Vendors', 'icon' => 'bi-shop', 'permission' => 'Vendors list', 'url' => route('vendors.index'), 'active' => Request::is('admin/vendors*')],
            ],
        ],
        [
            'label' => 'System Settings',
            'items' => [
                ['label' => 'General Settings', 'icon' => 'bi-sliders', 'permission' => 'Settings list', 'url' => route('settings.index'), 'active' => Request::is('admin/settings*')],
            ],
        ],
        [
            'label' => 'Logistics Reference',
            'items' => [
                ['label' => 'Countries', 'icon' => 'bi-globe-americas', 'permission' => 'MasterData list', 'url' => route('logistics.master.index', ['entity' => 'countries']), 'active' => Request::is('admin/logistics/master/countries*')],
                ['label' => 'Currencies', 'icon' => 'bi-currency-exchange', 'permission' => 'MasterData list', 'url' => route('logistics.master.index', ['entity' => 'currencies']), 'active' => Request::is('admin/logistics/master/currencies*')],
                ['label' => 'Exchange Rates', 'icon' => 'bi-graph-up-arrow', 'permission' => 'MasterData list', 'url' => route('logistics.master.index', ['entity' => 'exchange-rates']), 'active' => Request::is('admin/logistics/master/exchange-rates*')],
                ['label' => 'Border Posts', 'icon' => 'bi-signpost-split', 'permission' => 'MasterData list', 'url' => route('logistics.master.index', ['entity' => 'border-posts']), 'active' => Request::is('admin/logistics/master/border-posts*')],
                ['label' => 'Trip Expense Categories', 'icon' => 'bi-receipt', 'permission' => 'MasterData list', 'url' => route('logistics.master.index', ['entity' => 'expense-categories']), 'active' => Request::is('admin/logistics/master/expense-categories*')],
                ['label' => 'Document Types', 'icon' => 'bi-file-earmark-text', 'permission' => 'MasterData list', 'url' => route('logistics.master.index', ['entity' => 'document-types']), 'active' => Request::is('admin/logistics/master/document-types*')],
            ],
        ],
    ];
@endphp

<nav class="md-nav" aria-label="Master Data">
    @foreach($masterGroups as $group)
        @php($groupItems = collect($group['items'])->filter(fn ($item) => auth()->user() && auth()->user()->can($item['permission'])))
        @if($groupItems->isNotEmpty())
            <div class="md-nav-group">
                <div class="md-nav-label">{{ $group['label'] }}</div>
                <ul class="nav flex-column">
                    @foreach($groupItems as $item)
                        <li class="nav-item">
                            <a class="nav-link {{ $item['active'] ? 'active' : '' }}" href="{{ $item['url'] }}">
                                <i class="bi {{ $item['icon'] }} me-2"></i>{{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endforeach
</nav>
