<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fournisseur extends Model
{
    use HasFactory;

    protected $table = 'fournisseurs';

    protected $fillable = [
        'nom',
        'code_fournisseur',
        'telephone',
        'email',
        'adresse',
        'ville',
        'delai_livraison_jours',
        'conditions_paiement',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'delai_livraison_jours' => 'integer',
            'actif' => 'boolean',
        ];
    }

    public function commandes(): HasMany
    {
        return $this->hasMany(CommandeFournisseur::class);
    }

    public function scopeActif(Builder $query): Builder
    {
        return $query->where('actif', true);
    }
}
