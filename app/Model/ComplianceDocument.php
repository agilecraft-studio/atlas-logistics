<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ComplianceDocument extends Model
{
    protected $table = 'compliance_documents';
    protected $guarded = ['id'];
    protected $casts = ['issue_date' => 'date', 'expiry_date' => 'date'];

    public function type()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    /** "vehicle:12" style value used by the form select (field name "owner"). */
    public function getOwnerAttribute()
    {
        return $this->owner_type ? $this->owner_type . ':' . $this->owner_id : null;
    }
}
