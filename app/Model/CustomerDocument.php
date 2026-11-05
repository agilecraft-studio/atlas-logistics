<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Operational customer document (delivery note, dispatch sheet, waybill, proof of delivery or
 * freight quote). Final invoices stay in the Invoice model; the customer hub merges both.
 */
class CustomerDocument extends Model
{
    use SoftDeletes;

    public const DELIVERY_NOTE  = 'delivery_note';
    public const DISPATCH_SHEET = 'dispatch_sheet';
    public const WAYBILL        = 'waybill';
    public const POD            = 'pod';
    public const QUOTE          = 'quote';

    protected $table = 'customer_documents';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'issue_date'  => 'date',
        'due_date'    => 'date',
        'valid_until' => 'date',
        'signed_at'   => 'datetime',
        'meta'        => 'array',
        'file_size'   => 'integer',
        'amount'      => 'decimal:2',
        'amount_base' => 'decimal:2',
    ];

    public function customer() { return $this->belongsTo(User::class, 'customer_id')->withTrashed(); }
    public function trip()     { return $this->belongsTo(Trip::class, 'trip_id')->withTrashed(); }
    public function driver()   { return $this->belongsTo(User::class, 'driver_id')->withTrashed(); }
    public function vehicle()  { return $this->belongsTo(VehicleModel::class, 'vehicle_id')->withTrashed(); }
    public function trailer()  { return $this->belongsTo(Trailer::class, 'trailer_id')->withTrashed(); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by')->withTrashed(); }
    public function updater()  { return $this->belongsTo(User::class, 'updated_by')->withTrashed(); }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOperational($query)
    {
        return $query->where('type', '!=', self::POD);
    }

    public function getTypeConfig(): array
    {
        return config('customer_documents.types.' . $this->type, []);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->getTypeConfig()['label'] ?? ucfirst(str_replace('_', ' ', (string) $this->type));
    }

    public function getTitleAttribute(): string
    {
        return $this->getTypeConfig()['title'] ?? strtoupper($this->type_label);
    }

    public function getStatusLabelAttribute(): string
    {
        return config('customer_documents.statuses.' . $this->status, ucfirst(str_replace('_', ' ', (string) $this->status)));
    }

    public function getHasFileAttribute(): bool
    {
        return $this->file_path !== null && Storage::disk('public')->exists($this->file_path);
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->has_file ? Storage::disk('public')->url($this->file_path) : null;
    }

    public function getIsImageAttribute(): bool
    {
        return $this->has_file && str_starts_with((string) $this->mime_type, 'image/');
    }

    public function getMetaValue(string $key, $default = null)
    {
        return data_get($this->meta, $key, $default);
    }
}
