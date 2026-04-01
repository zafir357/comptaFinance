<?php

namespace App\Http\Requests\Expenses;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'vat_amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['nullable', 'string', 'max:100'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre de la dépense est obligatoire.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.min' => 'Le montant doit être positif.',
            'date.required' => 'La date est obligatoire.',
            'date.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'receipt.mimes' => 'Le reçu doit être au format JPG, PNG ou PDF.',
            'receipt.max' => 'Le reçu ne peut pas dépasser 5 Mo.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('amount')) {
            $this->merge([
                'amount' => (float) str_replace([' ', ','], ['', '.'], $this->amount),
            ]);
        }

        if ($this->has('vat_amount')) {
            $this->merge([
                'vat_amount' => (float) str_replace([' ', ','], ['', '.'], $this->vat_amount),
            ]);
        }
    }
}
