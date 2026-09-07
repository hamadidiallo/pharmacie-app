<?php

namespace App\Models;

use App\Enums\StatutCommandeFournisseur;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CommandeFournisseur extends Model
{
    use HasFactory;

    protected $table = 'commandes_fournisseur';

    protected $fillable = [
        'fournisseur_id',
        'user_id',
        'reference',
        'date_commande',
        'date_livraison_prevue',
        'date_reception',
        'numero_bl',
        'statut',
        'total_estime',
        'total_facture',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_commande' => 'date',
            'date_livraison_prevue' => 'date',
            'date_reception' => 'date',
            'total_estime' => 'decimal:2',
            'total_facture' => 'decimal:2',
            'statut' => StatutCommandeFournisseur::class,
        ];
    }

    public static function genererReference(): string
    {
        $annee = now()->format('Y');
        $compte = self::whereYear('created_at', $annee)->count() + 1;
        return sprintf('BC-%s-%04d', $annee, $compte);
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(CommandeFournisseurLigne::class);
    }

    public function estModifiable(): bool
    {
        return $this->statut === StatutCommandeFournisseur::Brouillon;
    }

    public function peutEtreEnvoyee(): bool
    {
        return $this->statut === StatutCommandeFournisseur::Brouillon && $this->lignes()->count() > 0;
    }

    public function peutEtreRecue(): bool
    {
        return in_array($this->statut, [
            StatutCommandeFournisseur::Envoyee,
            StatutCommandeFournisseur::PartiellementRecue,
        ], true);
    }

    public function recalculerTotalEstime(): self
    {
        $this->total_estime = $this->lignes->sum(fn ($ligne) => $ligne->sousTotalEstime());
        $this->save();

        return $this;
    }
}
