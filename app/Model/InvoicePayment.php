<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoicePayment extends Model
{
    use SoftDeletes;

    protected $table = 'invoice_payments';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    protected $casts = ['paid_at' => 'date'];

    public function invoice()    { return $this->belongsTo(Invoice::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by')->withTrashed(); }

    public function getMethodLabelAttribute(): string
    {
        return config('logistics.payment_methods.' . $this->method, ucfirst(str_replace('_', ' ', $this->method)));
    }
}
