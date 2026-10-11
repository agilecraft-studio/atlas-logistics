<?php
return ['sections' => [
['label' => 'WORKSPACE', 'items' => [
['label' => 'Dashboard', 'icon' => 'bi-grid-1x2-fill', 'route' => 'logistics.dashboard', 'permission' => 'LogisticsReports list', 'active' => 'logistics.dashboard'],
]],
['label' => 'OPERATIONS', 'items' => [
['label' => 'Fleet', 'icon' => 'bi-truck-front-fill', 'route' => 'vehicles.index', 'permission' => 'Vehicles list', 'active' => 'vehicles.*'],
['label' => 'Trips & Dispatch', 'icon' => 'bi-signpost-2-fill', 'route' => 'logistics.trips.index', 'permission' => 'Trips list', 'active' => 'logistics.trips.*'],
['label' => 'Drivers', 'icon' => 'bi-person-badge', 'route' => 'drivers.index', 'permission' => 'Drivers list', 'active' => 'drivers.*'],
['label' => 'Trip Expenses', 'icon' => 'bi-cash-stack', 'route' => 'logistics.expenses.index', 'permission' => 'TripExpenses list', 'active' => 'logistics.expenses.*'],
['label' => 'Advances', 'icon' => 'bi-wallet2', 'route' => 'logistics.advances.index', 'permission' => 'Advances list', 'active' => 'logistics.advances.*'],
['label' => 'Fuel', 'icon' => 'bi-fuel-pump-fill', 'route' => 'fuel.index', 'permission' => 'Fuel list', 'active' => 'fuel.*'],
['label' => 'Trailers', 'icon' => 'bi-truck-flatbed', 'route' => 'logistics.trailers.index', 'permission' => 'Trailers list', 'active' => 'logistics.trailers.*'],
['label' => 'Components', 'icon' => 'bi-gear-fill', 'route' => 'logistics.components.index', 'permission' => 'FleetRegister list', 'active' => 'logistics.components.*'],
['label' => 'Breakdown', 'icon' => 'bi-exclamation-triangle-fill', 'route' => 'vehicle-breakdown.index', 'permission' => 'VehicleBreakdown list', 'active' => 'vehicle-breakdown.*'],
['label' => 'Service Reminders', 'icon' => 'bi-alarm-fill', 'route' => 'service-reminder.index', 'permission' => 'ServiceReminders list', 'active' => 'service-reminder.*'],
['label' => 'Service Items', 'icon' => 'bi-list-check', 'route' => 'service-item.index', 'permission' => 'ServiceItems list', 'active' => 'service-item.*'],
['label' => 'Mechanics', 'icon' => 'bi-person-gear', 'route' => 'mechanic.index', 'permission' => 'Mechanics list', 'active' => 'mechanic.*'],
['label' => 'Work Orders', 'icon' => 'bi-wrench-adjustable', 'route' => 'work_order.index', 'permission' => 'Parts list', 'active' => 'work_order.*'],
]],
['label' => 'FINANCE', 'items' => [
['label' => 'Profitability', 'icon' => 'bi-bar-chart-line-fill', 'route' => 'logistics.reports.profitability', 'permission' => 'LogisticsReports list', 'active' => 'logistics.reports.*'],
['label' => 'Income', 'icon' => 'bi-graph-up-arrow', 'route' => 'income.index', 'permission' => 'Income list', 'active' => 'income.*'],
['label' => 'Vehicle Expenses', 'icon' => 'bi-receipt', 'route' => 'expense.index', 'permission' => 'Expense list', 'active' => 'expense.*'],
['label' => 'Parts', 'icon' => 'bi-box-seam-fill', 'route' => 'parts.index', 'permission' => 'Parts list', 'active' => 'parts.*'],
['label' => 'Parts Categories', 'icon' => 'bi-boxes', 'route' => 'parts-category.index', 'permission' => 'PartsCategory list', 'active' => 'parts-category.*'],
]],
['label' => 'CONTROL', 'items' => [
['label' => 'Compliance', 'icon' => 'bi-file-earmark-check-fill', 'route' => 'logistics.compliance', 'permission' => 'Compliance list', 'active' => 'logistics.compliance*'],
['label' => 'Reports', 'icon' => 'bi-clipboard-data-fill', 'route' => 'reports.monthly', 'permission' => 'Reports list', 'active' => 'reports.*'],
['label' => 'Notes', 'icon' => 'bi-journal-text', 'route' => 'notes.index', 'permission' => 'Notes list', 'active' => 'notes.*'],
['label' => 'Driver Alerts', 'icon' => 'bi-bell-fill', 'route' => 'driver-alert.index', 'permission' => 'DriverAlert list', 'active' => 'driver-alert.*'],
['label' => 'Telematics', 'icon' => 'bi-geo-alt-fill', 'route' => 'traccar.settings', 'permission' => 'Settings list', 'active' => 'traccar.*'],
]],
['label' => 'ADMIN', 'items' => [
['label' => 'Users', 'icon' => 'bi-people', 'route' => 'users.index', 'permission' => 'Users list', 'active' => 'users.*'],
['label' => 'Roles', 'icon' => 'bi-shield-lock-fill', 'route' => 'roles.index', 'permission' => 'Roles list', 'active' => 'roles.*'],
['label' => 'Settings', 'icon' => 'bi-gear', 'route' => 'settings', 'permission' => 'Settings list', 'active' => 'settings*'],
['label' => 'Addresses', 'icon' => 'bi-pin-map-fill', 'route' => 'addresses.index', 'permission' => 'Customer list', 'active' => 'addresses*'],
]],
]];
