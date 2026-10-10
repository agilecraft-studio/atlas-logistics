<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DriverAdvance extends Model
{
    protected $table = 'driver_advances';
    protected $guarded = ['id'];
    protected $casts = ['issued_at' => 'date'];

    public function trip()   { return $this->belongsTo(Trip::class); }
    public function driver() { return $this->belongsTo(User::class, 'driver_id')->withTrashed(); }
}
