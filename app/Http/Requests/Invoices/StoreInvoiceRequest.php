<?php

namespace App\Http\Requests\Invoices;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('Invoices add');
    }

    public function rules(): array
    {
        return [
            'document_type' => ['nullable', Rule::in(array_keys(config('invoicing.document_types')))],
            'source_invoice_id' => [
                'nullable', 'integer',
                Rule::exists('invoices', 'id')->whereNull('deleted_at'),
            ],
            'credit_reason' => ['nullable', 'string', 'max:2000'],
            'client_id' => [
                'required', 'integer',
                Rule::exists('users', 'id')->where('user_type', 'C')->whereNull('deleted_at'),
            ],
            'currency_code' => [
                'required', 'string', 'size:3',
                Rule::exists('currencies', 'code')->where('is_active', true),
            ],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'exchange_rate' => ['nullable', 'numeric', 'gt:0', 'max:1000000'],
            'rate_locked' => ['nullable', 'boolean'],
            'payment_instructions' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'trip_ids' => ['nullable', 'array', 'max:50'],
            'trip_ids.*' => [
                'integer', 'distinct',
                Rule::exists('trips', 'id')->whereIn('status', ['completed', 'closed'])->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'trip_ids.*.exists' => 'Invoices can only be raised from completed trips.',
            'client_id.exists' => 'Choose a customer account.',
        ];
    }
}
