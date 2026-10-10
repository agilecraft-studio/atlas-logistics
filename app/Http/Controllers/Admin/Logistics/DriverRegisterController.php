<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Model\ComplianceDocument;
use App\Model\Country;
use App\Model\DriverComplianceProfile;
use App\Model\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

/** Driver register: licence / passport / medical dates, approved countries and status per driver. */
class DriverRegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:FleetRegister list');
        $this->middleware('permission:FleetRegister edit', ['only' => ['edit', 'update']]);
    }

    /** Cell for a date: red if expired, amber if within 30 days. */
    protected function dateCell($date)
    {
        if (!$date) {
            return '—';
        }
        $left = (int) Carbon::today()->diffInDays($date->copy()->startOfDay(), false);
        $text = $date->format('d M Y');
        if ($left < 0) { return ['badge' => 'danger', 'text' => $text . ' (expired)']; }
        if ($left <= 30) { return ['badge' => 'warning', 'text' => $text . " ({$left}d)"]; }
        return $text;
    }

    public function index()
    {
        $profiles = DriverComplianceProfile::all()->keyBy('user_id');
        $docs = ComplianceDocument::where('owner_type', 'driver')->get()->groupBy('owner_id');

        $rows = [];
        foreach (User::where('user_type', 'D')->orderBy('name')->get() as $u) {
            $p = $profiles->get($u->id);
            $rows[] = [
                'cells' => [
                    $u->name,
                    $p && $p->licence_number ? $p->licence_number . ($p->licence_class ? ' (' . $p->licence_class . ')' : '') : '—',
                    $p ? $this->dateCell($p->licence_expiry) : ['badge' => 'light', 'text' => 'No profile'],
                    $p ? $this->dateCell($p->passport_expiry) : '—',
                    $p ? $this->dateCell($p->medical_expiry) : '—',
                    $p ? ucfirst($p->driver_status) : '—',
                    $p ? (string) count((array) $p->approved_country_ids) : '0',
                    (string) $docs->get($u->id, collect())->count(),
                ],
                'links' => [
                    ['label' => 'Compliance profile', 'url' => route('logistics.register.drivers.edit', ['id' => $u->id]), 'class' => 'btn-info'],
                    ['label' => 'Documents', 'url' => route('logistics.documents.index', ['owner' => 'driver:' . $u->id]), 'class' => 'btn-light'],
                ],
            ];
        }

        return view('logistics.registers.list', [
            'title'   => 'Driver Register',
            'headers' => ['Driver', 'Licence', 'Licence expiry', 'Passport expiry', 'Medical expiry', 'Status', 'Countries', 'Documents'],
            'rows'    => $rows,
            'intro'   => 'Names come from Drivers. Add each driver\'s licence, passport and approved countries so trips can be checked before dispatch.',
            'extra'   => [['label' => 'Manage drivers', 'url' => url('admin/drivers')]],
        ]);
    }

    protected function fields(): array
    {
        return [
            ['name' => 'employee_number', 'label' => 'Employee number', 'rules' => 'nullable|string|max:50', 'col' => 3],
            ['name' => 'national_id', 'label' => 'National ID number', 'rules' => 'nullable|string|max:50', 'col' => 3],
            ['name' => 'driver_status', 'label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,on_leave,suspended,terminated', 'col' => 3,
                'options' => ['active' => 'Active', 'on_leave' => 'On leave', 'suspended' => 'Suspended', 'terminated' => 'Terminated']],
            ['name' => 'home_depot', 'label' => 'Home depot', 'rules' => 'nullable|string|max:100', 'col' => 3],
            ['name' => 'licence_number', 'label' => 'Driving licence number', 'rules' => 'nullable|string|max:50', 'col' => 4],
            ['name' => 'licence_class', 'label' => 'Licence class', 'rules' => 'nullable|string|max:20', 'col' => 2],
            ['name' => 'licence_expiry', 'label' => 'Licence expiry', 'type' => 'date', 'rules' => 'nullable|date', 'col' => 3],
            ['name' => 'medical_expiry', 'label' => 'Medical certificate expiry', 'type' => 'date', 'rules' => 'nullable|date', 'col' => 3],
            ['name' => 'passport_number', 'label' => 'Passport number', 'rules' => 'nullable|string|max:50', 'col' => 4],
            ['name' => 'passport_expiry', 'label' => 'Passport expiry', 'type' => 'date', 'rules' => 'nullable|date', 'col' => 4],
            ['name' => 'yellow_fever_expiry', 'label' => 'Yellow fever card expiry', 'type' => 'date', 'rules' => 'nullable|date', 'col' => 4],
            ['name' => 'approved_country_ids', 'label' => 'Countries this driver may enter (hold Ctrl/Cmd to select several; leave empty = not restricted)',
                'type' => 'multiselect', 'options' => Country::where('is_active', true)->orderBy('name')->pluck('name', 'id')->all(), 'rules' => 'nullable|array', 'col' => 6],
            ['name' => 'emergency_contact_name', 'label' => 'Emergency contact', 'rules' => 'nullable|string|max:100', 'col' => 3],
            ['name' => 'emergency_contact_phone', 'label' => 'Emergency phone', 'type' => 'tel', 'rules' => 'nullable|string|max:30', 'col' => 3],
            ['name' => 'payment_details', 'label' => 'Bank / mobile money details', 'rules' => 'nullable|string|max:150', 'col' => 6],
            ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000', 'col' => 12],
        ];
    }

    public function edit($id)
    {
        $driver = User::where('user_type', 'D')->findOrFail($id);
        $profile = DriverComplianceProfile::firstOrNew(['user_id' => $driver->id]);
        if (!$profile->exists) {
            $profile->driver_status = 'active';
        }

        return view('logistics.crud.form', [
            'title'  => 'Compliance profile - ' . $driver->name,
            'fields' => $this->fields(),
            'model'  => $profile,
            'action' => route('logistics.register.drivers.update', ['id' => $driver->id]),
            'method' => 'PUT',
            'back'   => route('logistics.register.drivers'),
            'intro'  => null,
        ]);
    }

    public function update(Request $request, $id)
    {
        $driver = User::where('user_type', 'D')->findOrFail($id);
        $rules = [];
        foreach ($this->fields() as $f) {
            $rules[$f['name']] = $f['rules'];
        }
        $data = $request->validate($rules);
        $data['approved_country_ids'] = array_values(array_map('intval', $data['approved_country_ids'] ?? []));
        DriverComplianceProfile::updateOrCreate(['user_id' => $driver->id], $data);

        return redirect()->route('logistics.register.drivers')->with('success', 'Driver compliance profile saved.');
    }
}
