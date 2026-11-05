<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trailer extends Model
{
    use SoftDeletes;
    protected $table = 'trailers';
    protected $guarded = ['id'];
    protected $casts = [
        'first_registration' => 'date',
        'year' => 'integer',
        'payload_capacity_kg' => 'integer',
        'tare_kg' => 'integer',
        'gvm_kg' => 'integer',
    ];
    public function getLabelAttribute() { return $this->registration_number . ($this->trailer_type ? ' (' . $this->trailer_type . ')' : ''); }

    public function complianceDocuments() {
        return $this->hasMany(ComplianceDocument::class, 'owner_id')->where('owner_type', 'trailer');
    }

    public function workOrders() {
        return $this->hasMany(WorkOrders::class, 'trailer_id')->withTrashed();
    }

    public function components() {
        return $this->hasMany(VehicleComponent::class, 'owner_id')->where('owner_type', 'trailer');
    }

    public function assignments() {
        return $this->hasMany(TrailerAssignment::class)->orderByDesc('assigned_at')->orderByDesc('id');
    }

    public function currentAssignment() {
        return $this->hasOne(TrailerAssignment::class)->whereNull('released_at')->latestOfMany();
    }

    public function trips() {
        return $this->hasMany(Trip::class, 'trailer_id')->withTrashed();
    }
}
