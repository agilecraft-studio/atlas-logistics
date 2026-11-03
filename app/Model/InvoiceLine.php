<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    protected $table = 'invoice_lines';
    protected $guarded = ['id'];

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function trip()    { return $this->belongsTo(Trip::class)->withTrashed(); }

    public function getLineTypeLabelAttribute(): string
    {
        return config('invoicing.line_types.' . $this->line_type, ucfirst(str_replace('_', ' ', $this->line_type)));
    }
}
