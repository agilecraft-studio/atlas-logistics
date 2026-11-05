<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ComplianceDocument extends Model
{
    protected $table = 'compliance_documents';
    protected $guarded = ['id'];
    protected $casts = ['issue_date' => 'date', 'expiry_date' => 'date',
        'first_registration_date' => 'date', 'coverage_country_ids' => 'array', 'archived_at' => 'datetime'];

    public function type()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    /** "vehicle:12" style value used by the form select (field name "owner"); company docs use "company:0". */
    public function getOwnerAttribute()
    {
        return $this->owner_type ? $this->owner_type . ':' . $this->owner_id : null;
    }

    public function scopeForOwner($q, string $type, $id)
    {
        return $q->where('owner_type', $type)->where('owner_id', $id);
    }

    public function scopeNotArchived($q)
    {
        return $q->whereNull('archived_at');
    }
}
