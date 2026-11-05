<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DriverRating extends Model
{
    protected $table = 'driver_ratings';
    protected $guarded = ['id'];
    protected $casts = ['rated_at' => 'date'];

    public function driver()   { return $this->belongsTo(User::class, 'driver_id')->withTrashed(); }
    public function trip()     { return $this->belongsTo(Trip::class)->withTrashed(); }
    public function customer() { return $this->belongsTo(User::class, 'customer_id')->withTrashed(); }
}
