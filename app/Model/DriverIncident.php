<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DriverIncident extends Model
{
    protected $table = 'driver_incidents';
    protected $guarded = ['id'];
    protected $casts = [
        'occurred_at' => 'datetime',
        'resolved_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public const TYPES = [
        'violation' => 'Traffic violation',
        'accident' => 'Accident',
        'weighbridge' => 'Weighbridge offence',
        'disciplinary' => 'Disciplinary action',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'open' => 'Open',
        'under_review' => 'Under review',
        'resolved' => 'Resolved',
        'contested' => 'Contested',
    ];

    public function driver() { return $this->belongsTo(User::class, 'driver_id')->withTrashed(); }
    public function trip()   { return $this->belongsTo(Trip::class)->withTrashed(); }
    public function creator() { return $this->belongsTo(User::class, 'created_by')->withTrashed(); }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function scopeActive($q) { return $q->whereNull('archived_at'); }
}
