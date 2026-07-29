<?php

namespace App\Http\Requests;

use App\Models\Vente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaiementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mode_paiement' => ['required', Rule::in(array_keys(Vente::MODES_PAIEMENT))],
            'montant_recu' => ['nullable', 'numeric', 'min:0', 'required_if:mode_paiement,especes'],
        ];
    }

    public function messages(): array
    {
        return [
            'mode_paiement.required' => 'Choisissez un mode de paiement.',
            'mode_paiement.in' => 'Ce mode de paiement n\'est pas reconnu.',
            'montant_recu.required_if' => 'Indiquez le montant reçu du client.',
            'montant_recu.numeric' => 'Le montant reçu doit être un nombre.',
            'montant_recu.min' => 'Le montant reçu ne peut pas être négatif.',
        ];
    }
}
