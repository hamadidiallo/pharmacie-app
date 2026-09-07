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

            // Tiers Payant / Assurance
            'avec_assurance' => ['nullable', 'boolean'],
            'assurance_id' => ['nullable', 'required_if:avec_assurance,1', 'exists:assurances,id'],
            'matricule_assure' => ['nullable', 'required_if:avec_assurance,1', 'string', 'max:100'],
            'nom_assure' => ['nullable', 'string', 'max:150'],
            'taux_couverture' => ['nullable', 'numeric', 'min:0', 'max:100'],

            // Ordonnancier réglementaire
            'nom_prescripteur' => ['nullable', 'string', 'max:150'],
            'specialite_prescripteur' => ['nullable', 'string', 'max:100'],
            'nom_patient' => ['nullable', 'string', 'max:150'],
            'age_patient' => ['nullable', 'integer', 'min:0', 'max:130'],
            'date_prescription' => ['nullable', 'date'],
            'posologie' => ['nullable', 'string'],
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
            'assurance_id.required_if' => 'Veuillez sélectionner un organisme d\'assurance pour le tiers payant.',
            'matricule_assure.required_if' => 'Le matricule ou N° d\'assuré est obligatoire pour le tiers payant.',
            'taux_couverture.numeric' => 'Le taux de couverture doit être un pourcentage valide.',
        ];
    }
}
