<?php

namespace App\Http\Requests\Invoices;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends StoreInvoiceRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // Make customer_id optional on update
        $rules['customer_id'] = 'nullable|integer|exists:customers,id';

        return $rules;
    }
}
