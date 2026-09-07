<?php

namespace App\Models;

use App\Enums\StatutSessionCaisse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SessionCaisse extends Model
{
    use HasFactory;

    protected $table = 'sessions_caisse';

    protected $fillable = [
        'user_id',
        'date_ouverture',
        'date_fermeture',
        'fond_caisse_ouverture',
        'total_especes_theorique',
        'total_mobile_money',
        'total_carte',
        'total_sorties_especes',
        'total_entrees_especes',
        'montant_reel_compte',
        'ecart_caisse',
        'billetage',
        'statut',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'date_ouverture' => 'datetime',
            'date_fermeture' => 'datetime',
            'fond_caisse_ouverture' => 'decimal:2',
            'total_especes_theorique' => 'decimal:2',
            'total_mobile_money' => 'decimal:2',
            'total_carte' => 'decimal:2',
            'total_sorties_especes' => 'decimal:2',
            'total_entrees_especes' => 'decimal:2',
            'montant_reel_compte' => 'decimal:2',
            'ecart_caisse' => 'decimal:2',
            'billetage' => 'array',
            'statut' => StatutSessionCaisse::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ventes(): HasMany
    {
        return $this->hasMany(Vente::class, 'session_caisse_id');
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvementCaisse::class, 'session_caisse_id');
    }

    public function scopeOuverte(Builder $query): Builder
    {
        return $query->where('statut', StatutSessionCaisse::Ouverte);
    }

    public function estOuverte(): bool
    {
        return $this->statut === StatutSessionCaisse::Ouverte;
    }

    /**
     * Solde théorique d'espèces attendu dans le tiroir-caisse :
     * Fond d'ouverture + Ventes en espèces + Apports d'appoint - Sorties/Dépenses
     */
    public function soldeTheoriqueAttendu(): float
    {
        return (float) $this->fond_caisse_ouverture
            + (float) $this->total_especes_theorique
            + (float) $this->total_entrees_especes
            - (float) $this->total_sorties_especes;
    }

    /**
     * Chiffre d'affaires global généré durant cette session (tous modes confondus).
     */
    public function totalChiffreAffaires(): float
    {
        return (float) $this->total_especes_theorique
            + (float) $this->total_mobile_money
            + (float) $this->total_carte;
    }

    /**
     * Recalcule dynamiquement les totaux à partir des ventes et des mouvements réels.
     */
    public function synchroniserTotaux(): self
    {
        $this->total_especes_theorique = (float) $this->ventes()->where('mode_paiement', 'especes')->sum('total');
        $this->total_mobile_money = (float) $this->ventes()->where('mode_paiement', 'mobile_money')->sum('total');
        $this->total_carte = (float) $this->ventes()->where('mode_paiement', 'carte')->sum('total');

        $this->total_sorties_especes = (float) $this->mouvements()->where('type', 'sortie')->sum('montant');
        $this->total_entrees_especes = (float) $this->mouvements()->where('type', 'entree')->sum('montant');

        $this->save();

        return $this;
    }
}
