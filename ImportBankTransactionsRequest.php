<?php

namespace App\Http\Requests\Banking;

use Illuminate\Foundation\Http\FormRequest;

class ImportBankTransactionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'csv_file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'csv_file.required' => 'Veuillez sélectionner un fichier CSV.',
            'csv_file.mimes' => 'Le fichier doit être au format CSV.',
            'csv_file.max' => 'Le fichier ne doit pas dépasser 10 MB.',
        ];
    }
}
