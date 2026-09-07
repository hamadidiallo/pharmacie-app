<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'taux_couverture_defaut',
        'telephone',
        'email',
        'adresse',
        'delai_remboursement_jours',
        'est_actif',
    ];

    protected $casts = [
        'taux_couverture_defaut' => 'float',
        'delai_remboursement_jours' => 'integer',
        'est_actif' => 'boolean',
    ];

    public function scopeActif($query)
    {
        return $query->where('est_actif', true);
    }

    public function ventes(): HasMany
    {
        return $this->hasMany(Vente::class);
    }

    public function bordereaux(): HasMany
    {
        return $this->hasMany(BordereauAssurance::class);
    }

    /**
     * Ventes en attente de facturation dans un bordereau.
     */
    public function ventesEnAttente()
    {
        return $this->hasMany(Vente::class)
            ->whereNull('bordereau_assurance_id')
            ->where('statut_remboursement', 'en_attente');
    }
}
