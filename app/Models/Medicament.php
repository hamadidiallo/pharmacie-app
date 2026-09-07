<?php

namespace App\Models;

use App\Enums\StatutStock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicament extends Model
{
    use HasFactory, SoftDeletes;

    /** En dessous de ce stock, le produit est signalé comme à recommander. */
    public const SEUIL_ALERTE = 5;

    /** Fenêtre, en jours, à partir de laquelle une expiration est signalée. */
    public const FENETRE_EXPIRATION_JOURS = 30;

    protected $fillable = [
        'nom',
        'dci',
        'code_barre',
        'forme',
        'dosage',
        'tableau',
        'ordonnance_requise',
        'prix',
        'stock',
        'stock_securite',
        'stock_alerte',
        'date_expiration',
        'description',
        'user_id',
    ];

    protected $casts = [
        'date_expiration' => 'date',
        'tableau' => \App\Enums\TableauReglementaire::class,
        'ordonnance_requise' => 'boolean',
        'stock_securite' => 'integer',
        'stock_alerte' => 'integer',
    ];

    public function lots()
    {
        return $this->hasMany(MedicamentLot::class);
    }

    public function lotsActifs()
    {
        return $this->hasMany(MedicamentLot::class)
            ->where('statut', \App\Enums\StatutLot::Actif)
            ->where('quantite_actuelle', '>', 0)
            ->where('date_expiration', '>', now())
            ->orderBy('date_expiration', 'asc');
    }

    public function prochainLot(): ?MedicamentLot
    {
        return $this->lotsActifs()->first();
    }

    /**
     * Recalcule le stock total disponible à la vente d'après les lots actifs.
     */
    public function synchroniserStockDepuisLots(): void
    {
        $totalActif = $this->lots()
            ->where('statut', \App\Enums\StatutLot::Actif)
            ->where('date_expiration', '>', now())
            ->sum('quantite_actuelle');

        $this->update(['stock' => $totalActif]);
    }

    public function ventes()
    {
        return $this->belongsToMany(
            Vente::class,
            'medicament__vente'
        )
            ->using(Medicament_Vente::class)
            ->withPivot('quantite', 'prix', 'sous_total')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ---- état du stock -------------------------------------------------

    protected function statutStock(): Attribute
    {
        return Attribute::get(fn (): StatutStock => match (true) {
            $this->stock <= 0 => StatutStock::Rupture,
            $this->stock <= self::SEUIL_ALERTE => StatutStock::Faible,
            default => StatutStock::Ok,
        });
    }

    public function expireBientot(): bool
    {
        return $this->date_expiration?->between(
            now(),
            now()->addDays(self::FENETRE_EXPIRATION_JOURS)
        ) ?? false;
    }

    // ---- filtres -------------------------------------------------------

    public function scopeEnRupture(Builder $requete): Builder
    {
        return $requete->where('stock', '<=', 0);
    }

    public function scopeStockFaible(Builder $requete): Builder
    {
        return $requete->where('stock', '>', 0)->where('stock', '<=', self::SEUIL_ALERTE);
    }

    public function scopeEnStock(Builder $requete): Builder
    {
        return $requete->where('stock', '>', self::SEUIL_ALERTE);
    }

    /** Le scope porte un autre nom que expireBientot() pour éviter la collision. */
    public function scopeProcheExpiration(Builder $requete): Builder
    {
        return $requete->whereBetween('date_expiration', [
            now(),
            now()->addDays(self::FENETRE_EXPIRATION_JOURS),
        ]);
    }

    /** Produits demandant une action : stock insuffisant ou expiration proche. */
    public function scopeASurveiller(Builder $requete): Builder
    {
        return $requete->where(
            fn (Builder $sous) => $sous
                ->where('stock', '<=', self::SEUIL_ALERTE)
                ->orWhereBetween('date_expiration', [
                    now(),
                    now()->addDays(self::FENETRE_EXPIRATION_JOURS),
                ])
        );
    }
}
