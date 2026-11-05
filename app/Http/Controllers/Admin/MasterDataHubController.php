<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class MasterDataHubController extends Controller
{
    public function index()
    {
        $groups = [
            [
                'label' => 'Fleet Configuration',
                'icon' => 'bi-truck',
                'description' => 'Vehicle types and the groups your fleet is organised into.',
                'items' => [
                    ['label' => 'Vehicle Types', 'permission' => 'VehicleType list', 'url' => route('vehicle-types.index')],
                    ['label' => 'Vehicle Groups', 'permission' => 'VehicleGroup list', 'url' => route('vehicle_group.index')],
                ],
            ],
            [
                'label' => 'Inventory & Services',
                'icon' => 'bi-box-seam',
                'description' => 'Parts stock, categories and the service intervals used by maintenance.',
                'items' => [
                    ['label' => 'Parts', 'permission' => 'Parts list', 'url' => route('parts.index')],
                    ['label' => 'Parts Categories', 'permission' => 'PartsCategory list', 'url' => route('parts-category.index')],
                    ['label' => 'Service Items', 'permission' => 'ServiceItems list', 'url' => route('service-item.index')],
                ],
            ],
            [
                'label' => 'Partners & Resources',
                'icon' => 'bi-people',
                'description' => 'Mechanics and the vendors that supply parts, fuel and services.',
                'items' => [
                    ['label' => 'Mechanics', 'permission' => 'Mechanics list', 'url' => route('mechanic.index')],
                    ['label' => 'Vendors', 'permission' => 'Vendors list', 'url' => route('vendors.index')],
                ],
            ],
            [
                'label' => 'System Settings',
                'icon' => 'bi-sliders',
                'description' => 'Company profile, localisation, compliance alerts and driver portal settings.',
                'items' => [
                    ['label' => 'General Settings', 'permission' => 'Settings list', 'url' => route('settings.index')],
                ],
            ],
            [
                'label' => 'Logistics Reference',
                'icon' => 'bi-globe',
                'description' => 'Countries, currencies, border posts, expense categories and document types.',
                'items' => [
                    ['label' => 'Countries', 'permission' => 'MasterData list', 'url' => route('logistics.master.index', ['entity' => 'countries'])],
                    ['label' => 'Currencies', 'permission' => 'MasterData list', 'url' => route('logistics.master.index', ['entity' => 'currencies'])],
                    ['label' => 'Exchange Rates', 'permission' => 'MasterData list', 'url' => route('logistics.master.index', ['entity' => 'exchange-rates'])],
                    ['label' => 'Border Posts', 'permission' => 'MasterData list', 'url' => route('logistics.master.index', ['entity' => 'border-posts'])],
                    ['label' => 'Trip Expense Categories', 'permission' => 'MasterData list', 'url' => route('logistics.master.index', ['entity' => 'expense-categories'])],
                    ['label' => 'Document Types', 'permission' => 'MasterData list', 'url' => route('logistics.master.index', ['entity' => 'document-types'])],
                ],
            ],
        ];

        return view('master-data.index', compact('groups'));
    }
}
