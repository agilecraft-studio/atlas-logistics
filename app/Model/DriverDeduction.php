<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DriverDeduction extends Model
{
    protected $table = 'driver_deductions';
    protected $guarded = ['id'];
    protected $casts = [
        'incurred_on' => 'date',
        'archived_at' => 'datetime',
    ];

    public const TYPES = [
        'loan' => 'Loan',
        'advance_recovery' => 'Advance recovery',
        'fine' => 'Fine / penalty',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'settled' => 'Settled',
        'waived' => 'Waived',
    ];

    public function driver() { return $this->belongsTo(User::class, 'driver_id')->withTrashed(); }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function scopeActive($q) { return $q->whereNull('archived_at'); }
}
