<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrailerAssignment extends Model
{
    use SoftDeletes;

    protected $table = 'trailer_assignments';
    protected $guarded = ['id'];
    protected $casts = [
        'assigned_at' => 'date',
        'released_at' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function trailer() { return $this->belongsTo(Trailer::class)->withTrashed(); }
    public function vehicle() { return $this->belongsTo(VehicleModel::class, 'vehicle_id')->withTrashed(); }
    public function driver()  { return $this->belongsTo(User::class, 'driver_id')->withTrashed(); }
    public function creator() { return $this->belongsTo(User::class, 'created_by')->withTrashed(); }

    public function scopeOpen($q) { return $q->whereNull('released_at'); }

    public function getIsCurrentAttribute(): bool
    {
        return $this->released_at === null;
    }
}
