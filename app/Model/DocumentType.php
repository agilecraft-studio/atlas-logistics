<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $table = 'document_types';
    protected $guarded = ['id'];
    protected $casts = ['is_critical' => 'boolean', 'is_active' => 'boolean'];
}
