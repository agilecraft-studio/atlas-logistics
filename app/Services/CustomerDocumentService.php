<?php

namespace App\Services;

use App\Model\AtlasLogistics;
use App\Model\CustomerDocument;
use App\Model\Trip;
use App\Model\User;
use App\Support\Money;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CustomerDocumentService
{
    public function types(): array
    {
        return config('customer_documents.types', []);
    }

    public function statusesFor(string $type): array
    {
        $keys = $this->types()[$type]['statuses'] ?? array_keys(config('customer_documents.statuses', []));

        return array_intersect_key(config('customer_documents.statuses', []), array_flip($keys));
    }

    public function create(User $customer, array $data, ?User $actor = null): CustomerDocument
    {
        return DB::transaction(function () use ($customer, $data, $actor) {
            $type = (string) ($data['type'] ?? '');
            if (!isset($this->types()[$type])) {
                throw ValidationException::withMessages(['type' => 'Choose a valid document type.']);
            }

            $trip = $this->resolveTrip($customer, $data['trip_id'] ?? null);
            $status = $this->statusFor($type, $data['status'] ?? null, $trip);
            $attributes = $this->attributes($customer, $type, $data, $trip, $status, $actor);

            if (!empty($data['file']) && $data['file'] instanceof UploadedFile) {
                $attributes = array_merge($attributes, $this->storeFile($customer, $data['file']));
            } elseif (($this->types()[$type]['file_required'] ?? false)) {
                throw ValidationException::withMessages(['file' => 'Upload the signed proof of delivery (PDF or image).']);
            }

            $attributes['document_number'] = $this->nextNumber($type);
            $document = CustomerDocument::create($attributes);
            $document->setRelation('trip', $trip);
            $document->setRelation('customer', $customer);

            return $document;
        });
    }

    public function update(CustomerDocument $document, array $data, ?User $actor = null): CustomerDocument
    {
        return DB::transaction(function () use ($document, $data, $actor) {
            $type = $document->type;
            $trip = $this->resolveTrip($document->customer, $data['trip_id'] ?? $document->trip_id);

            $status = $this->statusFor($type, $data['status'] ?? $document->status, $trip);
            $attributes = $this->attributes($document->customer, $type, $data, $trip, $status, $actor, $document);

            if (!empty($data['file']) && $data['file'] instanceof UploadedFile) {
                $attributes = array_merge($attributes, $this->storeFile($document->customer, $data['file']));
            }

            $document->fill($attributes)->save();

            return $document->fresh();
        });
    }

    /** Resolve the trip and make sure it belongs to the customer being documented. */
    public function resolveTrip(User $customer, $tripId): ?Trip
    {
        if ($tripId === null || $tripId === '') {
            return null;
        }

        $trip = Trip::with(['vehicle', 'trailer', 'driver', 'originCountry', 'destinationCountry'])
            ->find((int) $tripId);

        if (!$trip) {
            throw ValidationException::withMessages(['trip_id' => 'Choose a shipment.']);
        }
        if ($trip->customer_id !== null && (int) $trip->customer_id !== (int) $customer->id) {
            throw ValidationException::withMessages(['trip_id' => 'That shipment belongs to another customer.']);
        }

        return $trip;
    }

    public function nextNumber(string $type): string
    {
        $prefix = (string) ($this->types()[$type]['prefix'] ?? 'DOC');
        $year = (int) now()->year;
        $now = now();

        DB::table('document_sequences')->insertOrIgnore([
            'prefix' => $prefix, 'year' => $year, 'last_number' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $row = DB::table('document_sequences')
            ->where('prefix', $prefix)->where('year', $year)
            ->lockForUpdate()->first();

        $next = ((int) $row->last_number) + 1;
        DB::table('document_sequences')
            ->where('prefix', $prefix)->where('year', $year)
            ->update(['last_number' => $next, 'updated_at' => $now]);

        return sprintf('%s-%d-%04d', $prefix, $year, $next);
    }

    /** Company header data shared by the generated document PDFs and print views. */
    public function company(): array
    {
        return [
            'name'     => AtlasLogistics::get('app_name') ?: 'Atlas Logistics',
            'address1' => AtlasLogistics::get('badd1'),
            'address2' => AtlasLogistics::get('badd2'),
            'city'     => AtlasLogistics::get('city'),
            'state'    => AtlasLogistics::get('state'),
            'country'  => AtlasLogistics::get('country'),
            'email'    => AtlasLogistics::get('email'),
            'phone'    => AtlasLogistics::get('phone'),
            'tax_no'   => AtlasLogistics::get('tax_no'),
            'logo'     => $this->logoDataUri(),
        ];
    }

    private function attributes(User $customer, string $type, array $data, ?Trip $trip, string $status, ?User $actor, ?CustomerDocument $existing = null): array
    {
        $existingMeta = $existing?->meta ?: [];
        $meta = array_merge($existingMeta, $this->snapshot($customer, $trip));

        $amount = $data['amount'] ?? null;
        $rate = $data['exchange_rate'] ?? null;
        $rate = ($rate !== null && $rate !== '' && is_numeric($rate) && (float) $rate > 0) ? (string) $rate : '1.000000';
        $amountBase = null;
        if ($amount !== null && $amount !== '') {
            $amountBase = Money::fromMinor(Money::applyRate(Money::toMinor($amount), $rate));
        }

        return [
            'customer_id'    => $customer->id,
            'trip_id'        => $trip?->id,
            'type'           => $type,
            'status'         => $status,
            'issue_date'     => $data['issue_date'] ?? $existing?->issue_date?->toDateString() ?? now()->toDateString(),
            'due_date'       => $data['due_date'] ?? null,
            'valid_until'    => $data['valid_until'] ?? null,
            'amount'         => $amount !== '' ? $amount : null,
            'currency_code'  => $amount !== null && $amount !== '' ? strtoupper((string) ($data['currency_code'] ?? '')) : null,
            'exchange_rate'  => $rate,
            'amount_base'    => $amountBase,
            'driver_id'      => $data['driver_id'] ?? $trip?->driver_id ?? $existing?->driver_id,
            'vehicle_id'     => $data['vehicle_id'] ?? $trip?->vehicle_id ?? $existing?->vehicle_id,
            'trailer_id'     => $data['trailer_id'] ?? $trip?->trailer_id ?? $existing?->trailer_id,
            'signed_by'      => $data['signed_by'] ?? null,
            'signed_at'      => $data['signed_at'] ?? null,
            'notes'          => $data['notes'] ?? null,
            'meta'           => $meta,
            'updated_by'     => $actor?->id,
            'created_by'     => $existing?->created_by ?? $actor?->id,
        ];
    }

    private function snapshot(User $customer, ?Trip $trip): array
    {
        return [
            'customer_name'    => $customer->name,
            'customer_email'   => $customer->email,
            'customer_phone'   => $customer->getMeta('mobno') ?: $customer->getMeta('phone'),
            'customer_address' => $customer->getMeta('address'),
            'trip_number'      => $trip?->trip_number,
            'route'            => $trip?->route_label,
            'origin'           => trim(($trip?->origin_city ?: '') . ' ' . (optional($trip?->originCountry)->name ?: '')),
            'destination'      => trim(($trip?->destination_city ?: '') . ' ' . (optional($trip?->destinationCountry)->name ?: '')),
            'cargo_description'=> $trip?->cargo_description,
            'cargo_weight_kg'  => $trip?->cargo_weight_kg,
            'consignee_name'   => $trip?->consignee_name,
            'consignee_phone'  => $trip?->consignee_phone,
            'driver_name'      => optional($trip?->driver)->name,
            'vehicle_plate'    => optional($trip?->vehicle)->license_plate,
            'trailer_plate'    => optional($trip?->trailer)->registration_number,
            'planned_start_at' => optional($trip?->planned_start_at)->toDateTimeString(),
            'actual_start_at'  => optional($trip?->actual_start_at)->toDateTimeString(),
            'actual_end_at'    => optional($trip?->actual_end_at)->toDateTimeString(),
        ];
    }

    private function statusFor(string $type, ?string $requested, ?Trip $trip): string
    {
        $allowed = array_keys($this->statusesFor($type));
        if ($requested !== null && in_array($requested, $allowed, true)) {
            return $requested;
        }

        if ($type === CustomerDocument::POD) {
            return $trip && in_array($trip->status, ['completed', 'closed'], true) ? 'delivered' : 'pending';
        }

        $mapped = $trip ? (config('customer_documents.trip_status_map.' . $trip->status)) : null;
        if ($mapped && in_array($mapped, $allowed, true)) {
            return $mapped;
        }

        return $allowed[0] ?? 'draft';
    }

    private function storeFile(User $customer, UploadedFile $file): array
    {
        $path = $file->store('customer-documents/' . $customer->id, 'public');

        return [
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    private function logoDataUri(): ?string
    {
        $logo = AtlasLogistics::get('logo_img');
        if (!$logo) {
            return null;
        }

        $path = public_path('assets/images/' . $logo);
        if (!is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}
