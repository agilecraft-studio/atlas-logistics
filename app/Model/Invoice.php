<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    public const DRAFT = 'draft';
    public const ISSUED = 'issued';
    public const PART_PAID = 'part_paid';
    public const PAID = 'paid';
    public const CANCELLED = 'cancelled';

    public const DOC_TAX_INVOICE = 'tax_invoice';
    public const DOC_PROFORMA = 'proforma';
    public const DOC_CREDIT_NOTE = 'credit_note';

    /** Statuses whose value counts as revenue for trip profitability. */
    public const REVENUE_STATUSES = [self::ISSUED, self::PART_PAID, self::PAID];

    protected $table = 'invoices';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'issue_date'   => 'date',
        'due_date'     => 'date',
        'issued_at'    => 'datetime',
        'cancelled_at' => 'datetime',
        'rate_locked'  => 'boolean',
    ];

    public function client()        { return $this->belongsTo(User::class, 'client_id')->withTrashed(); }
    public function lines()         { return $this->hasMany(InvoiceLine::class)->orderBy('sort_order')->orderBy('id'); }
    public function payments()      { return $this->hasMany(InvoicePayment::class)->orderByDesc('paid_at')->orderByDesc('id'); }
    public function auditLogs()     { return $this->hasMany(InvoiceAuditLog::class)->orderByDesc('id'); }
    public function creator()       { return $this->belongsTo(User::class, 'created_by')->withTrashed(); }
    public function issuer()        { return $this->belongsTo(User::class, 'issued_by')->withTrashed(); }
    public function canceller()     { return $this->belongsTo(User::class, 'cancelled_by')->withTrashed(); }
    public function sourceInvoice() { return $this->belongsTo(self::class, 'source_invoice_id')->withTrashed(); }
    public function creditNotes()   { return $this->hasMany(self::class, 'source_invoice_id')->orderByDesc('id'); }

    public function isDraft(): bool
    {
        return $this->status === self::DRAFT;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::CANCELLED;
    }

    public function isLocked(): bool
    {
        return !$this->isDraft();
    }

    public function canTakePayments(): bool
    {
        return $this->isTaxInvoice()
            && in_array($this->status, [self::ISSUED, self::PART_PAID], true);
    }

    public function isTaxInvoice(): bool
    {
        return ($this->document_type ?: self::DOC_TAX_INVOICE) === self::DOC_TAX_INVOICE;
    }

    public function isProforma(): bool
    {
        return $this->document_type === self::DOC_PROFORMA;
    }

    public function isCreditNote(): bool
    {
        return $this->document_type === self::DOC_CREDIT_NOTE;
    }

    public function getStatusLabelAttribute(): string
    {
        return config('invoicing.statuses.' . $this->status, ucfirst(str_replace('_', ' ', $this->status)));
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return config('invoicing.document_types.' . $this->document_type . '.label', 'Tax invoice');
    }

    public function getDocumentTitleAttribute(): string
    {
        return config('invoicing.document_types.' . $this->document_type . '.title', 'INVOICE');
    }

    public function getDocumentIconAttribute(): string
    {
        return config('invoicing.document_types.' . $this->document_type . '.icon', 'bi-receipt');
    }

    public function scopeOfType($query, ?string $type)
    {
        return $type ? $query->where('document_type', $type) : $query;
    }

    public function scopeRevenueDocuments($query)
    {
        return $query->where('document_type', self::DOC_TAX_INVOICE);
    }

    /** Distinct trips referenced by the invoice lines. */
    public function linkedTrips()
    {
        $this->loadMissing('lines.trip');

        return $this->lines->pluck('trip')->filter()->unique('id')->values();
    }

    public function scopeRevenueBearing($query)
    {
        return $query->whereIn('status', self::REVENUE_STATUSES)
            ->where('document_type', self::DOC_TAX_INVOICE);
    }
}
