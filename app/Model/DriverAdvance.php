<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverAdvance extends Model
{
    use SoftDeletes;

    protected $table = 'driver_advances';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    protected $casts = ['issued_at' => 'date'];

    public function trip()      { return $this->belongsTo(Trip::class); }
    public function driver()    { return $this->belongsTo(User::class, 'driver_id')->withTrashed(); }
    public function issuedBy()  { return $this->belongsTo(User::class, 'issued_by')->withTrashed(); }
    public function deletedBy() { return $this->belongsTo(User::class, 'deleted_by')->withTrashed(); }
}
