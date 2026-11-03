<?php

namespace App\Http\Requests\Invoices;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('Invoices edit');
    }

    public function rules(): array
    {
        return [
            'client_id' => [
                'required', 'integer',
                Rule::exists('users', 'id')->where('user_type', 'C')->whereNull('deleted_at'),
            ],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'exchange_rate' => ['nullable', 'numeric', 'gt:0', 'max:1000000'],
            'rate_locked' => ['nullable', 'boolean'],
            'payment_instructions' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return ['client_id.exists' => 'Choose a customer account.'];
    }
}
