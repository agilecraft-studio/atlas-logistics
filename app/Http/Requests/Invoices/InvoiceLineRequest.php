<?php

namespace App\Http\Requests\Invoices;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('Invoices edit');
    }

    public function rules(): array
    {
        return [
            'line_type' => ['required', Rule::in(array_keys(config('invoicing.line_types')))],
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'trip_id' => [
                'nullable', 'integer',
                Rule::exists('trips', 'id')->whereIn('status', ['completed', 'closed'])->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return ['trip_id.exists' => 'Choose a completed trip.'];
    }
}
