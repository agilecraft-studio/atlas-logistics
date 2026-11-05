<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelCard extends Model
{
    use SoftDeletes;

    protected $table = 'fuel_cards';
    protected $fillable = [
        'label', 'card_number', 'vendor_id', 'vehicle_id', 'driver_id',
        'currency_code', 'monthly_limit', 'balance', 'status', 'expires_on',
        'note', 'created_by',
    ];

    protected $casts = ['expires_on' => 'date'];

    public const STATUSES = [
        'active' => 'Active',
        'suspended' => 'Suspended',
        'expired' => 'Expired',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id')->withTrashed();
    }

    public function vehicle()
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_id')->withTrashed();
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id')->withTrashed();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }
}
