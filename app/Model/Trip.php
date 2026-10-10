<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use SoftDeletes;

    protected $table = 'trips';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'planned_start_at'  => 'datetime',
        'planned_end_at'    => 'datetime',
        'actual_start_at'   => 'datetime',
        'actual_end_at'     => 'datetime',
        'closed_at'         => 'datetime',
        'route_country_ids' => 'array',
    ];

    public function vehicle()            { return $this->belongsTo(VehicleModel::class, 'vehicle_id')->withTrashed(); }
    public function trailer()            { return $this->belongsTo(Trailer::class, 'trailer_id')->withTrashed(); }
    public function driver()             { return $this->belongsTo(User::class, 'driver_id')->withTrashed(); }
    public function coDriver()           { return $this->belongsTo(User::class, 'co_driver_id')->withTrashed(); }
    public function customer()           { return $this->belongsTo(User::class, 'customer_id')->withTrashed(); }
    public function originCountry()      { return $this->belongsTo(Country::class, 'origin_country_id'); }
    public function destinationCountry() { return $this->belongsTo(Country::class, 'destination_country_id'); }
    public function checkpoints()        { return $this->hasMany(TripCheckpoint::class)->orderBy('arrived_at'); }
    public function expenses()           { return $this->hasMany(TripExpense::class)->orderByDesc('expense_date'); }
    public function advances()           { return $this->hasMany(DriverAdvance::class)->orderByDesc('issued_at'); }
    public function incidents()          { return $this->hasMany(TripIncident::class)->orderByDesc('occurred_at'); }

    public function getStatusLabelAttribute()
    {
        return config('logistics.trip_statuses.' . $this->status, ucfirst($this->status));
    }

    public function getRouteLabelAttribute()
    {
        $o = trim(($this->origin_city ?: '') . ' ' . optional($this->originCountry)->iso2);
        $d = trim(($this->destination_city ?: '') . ' ' . optional($this->destinationCountry)->iso2);
        return ($o ?: '?') . ' → ' . ($d ?: '?');
    }
}
