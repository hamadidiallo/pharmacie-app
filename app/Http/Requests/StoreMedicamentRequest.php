<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicamentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
             'nom' => 'required|string|max:191',
             'dci' => 'nullable|string|max:150',
             'code_barre' => 'nullable|string|max:60',
             'forme' => 'nullable|string|max:80',
             'dosage' => 'nullable|string|max:80',
             'tableau' => 'nullable|string',
             'ordonnance_requise' => 'nullable|boolean',
             'prix' => 'required|numeric|min:0',
             'stock' => 'required|numeric|min:0',
             'stock_securite' => 'nullable|integer|min:0',
             'numero_lot' => 'nullable|string|max:80',
             'date_expiration' => 'required|date',
             'description' => 'nullable|string',
        ];
    }
    public function messages(): array 
    {
        return [
            'nom.required' => 'le nom du medicament est obligatoire',
            'prix.required' => 'le prix du medicament est obligatoire',
            'stock.required' => 'la quantité du medicament est obligatoire',
            'stock.numeric' => 'la quantité du medicament doit être un nombre',
            'stock.min' => 'la quantité du medicament doit être supérieur à 0',
            'date_expiration.required' => 'la date d\'expiration du medicament est obligatoire',
            'date_expiration.date' => 'la date d\'expiration du medicament doit être une date',
            'description.required' => 'la description du medicament est obligatoire'

        ];
    }
}
