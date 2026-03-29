<?php

namespace App\Http\Requests\Invoices;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FORM REQUEST: StoreInvoiceRequest
 *
 * Validation for creating a new invoice.
 */
class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy will handle authorization
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'issue_date' => 'required|date_format:Y-m-d',
            'due_date' => 'required|date_format:Y-m-d|after_or_equal:issue_date',
            'notes' => 'nullable|string|max:500',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.quantity' => 'required|numeric|min:0.01|max:999999.99',
            'lines.*.unit_price' => 'required|numeric|min:0|max:999999.99',
            'lines.*.vat_rate' => 'required|integer|in:0,550,2000,5500,2010', // 0%, 5.5%, 20%, 55%, 20.1%
        ];
    }

    public function messages(): array
    {
        return [
            'due_date.after_or_equal' => 'Due date must be after or equal to issue date.',
            'lines.required' => 'At least one line item is required.',
            'lines.min' => 'At least one line item is required.',
        ];
    }
}
