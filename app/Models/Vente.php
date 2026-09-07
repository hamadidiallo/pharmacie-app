<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    public const MODES_PAIEMENT = [
        'especes' => 'Espèces',
        'mobile_money' => 'Mobile Money',
        'carte' => 'Carte',
    ];

    protected $fillable = [
        'total',
        'date_vente',
        'user_id',
        'session_caisse_id',
        'assurance_id',
        'bordereau_assurance_id',
        'matricule_assure',
        'nom_assure',
        'taux_couverture',
        'part_assurance',
        'part_patient',
        'statut_remboursement',
        'mode_paiement',
        'montant_recu',
        'monnaie_rendue',
    ];

    protected $casts = [
        'date_vente' => 'datetime',
        'total' => 'float',
        'taux_couverture' => 'float',
        'part_assurance' => 'float',
        'part_patient' => 'float',
    ];

    public function medicaments()
    {
        return $this->belongsToMany(
            Medicament::class,
            'medicament__vente'
        )
            ->using(Medicament_Vente::class)
            // un produit archivé doit rester visible sur les tickets déjà émis
            ->withTrashed()
            ->withPivot('quantite', 'prix', 'sous_total')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sessionCaisse()
    {
        return $this->belongsTo(SessionCaisse::class, 'session_caisse_id');
    }

    public function assurance()
    {
        return $this->belongsTo(Assurance::class);
    }

    public function bordereauAssurance()
    {
        return $this->belongsTo(BordereauAssurance::class, 'bordereau_assurance_id');
    }

    public function ordonnancierLignes()
    {
        return $this->hasMany(OrdonnancierLigne::class);
    }

    public function estPriseEnCharge(): bool
    {
        return $this->assurance_id !== null && $this->part_assurance > 0;
    }

    public function montantDuPatient(): float
    {
        return $this->part_patient ?? (float) $this->total;
    }

    public function montantAssurance(): float
    {
        return (float) ($this->part_assurance ?? 0);
    }

    public function libelleModePaiement(): string
    {
        return self::MODES_PAIEMENT[$this->mode_paiement] ?? $this->mode_paiement;
    }
}
