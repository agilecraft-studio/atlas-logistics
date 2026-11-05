<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DriverPayrollProfile extends Model
{
    protected $table = 'driver_payroll_profiles';
    protected $guarded = ['id'];

    public function driver()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }
}
