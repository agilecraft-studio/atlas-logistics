<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TripIncident extends Model
{
    protected $table = 'trip_incidents';
    protected $guarded = ['id'];
    protected $casts = ['occurred_at' => 'datetime', 'resolved_at' => 'datetime'];

    public function trip()    { return $this->belongsTo(Trip::class); }
    public function country() { return $this->belongsTo(Country::class, 'country_id'); }
}
