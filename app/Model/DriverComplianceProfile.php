<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DriverComplianceProfile extends Model
{
    protected $table = 'driver_compliance_profiles';
    protected $guarded = ['id'];
    protected $casts = [
        'passport_expiry'      => 'date',
        'licence_expiry'       => 'date',
        'yellow_fever_expiry'  => 'date',
        'medical_expiry'       => 'date',
        'approved_country_ids' => 'array',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
