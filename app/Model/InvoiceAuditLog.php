<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InvoiceAuditLog extends Model
{
    protected $table = 'invoice_audit_logs';
    protected $guarded = ['id'];
    protected $casts = ['details' => 'array'];

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function user()    { return $this->belongsTo(User::class, 'user_id')->withTrashed(); }
}
