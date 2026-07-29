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
        'mode_paiement',
        'montant_recu',
        'monnaie_rendue',
    ];

    protected $casts = [
        'date_vente' => 'datetime',
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

    public function libelleModePaiement(): string
    {
        return self::MODES_PAIEMENT[$this->mode_paiement] ?? $this->mode_paiement;
    }
}
