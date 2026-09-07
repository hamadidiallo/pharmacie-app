<?php

namespace App\Models;

use App\Enums\StatutLot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicamentLot extends Model
{
    use HasFactory;

    protected $table = 'medicament_lots';

    protected $fillable = [
        'medicament_id',
        'numero_lot',
        'date_fabrication',
        'date_expiration',
        'quantite_initiale',
        'quantite_actuelle',
        'prix_achat_unitaire',
        'statut',
        'motif_isolement',
    ];

    protected function casts(): array
    {
        return [
            'date_fabrication' => 'date',
            'date_expiration' => 'date',
            'quantite_initiale' => 'integer',
            'quantite_actuelle' => 'integer',
            'prix_achat_unitaire' => 'decimal:2',
            'statut' => StatutLot::class,
        ];
    }

    public function medicament(): BelongsTo
    {
        return $this->belongsTo(Medicament::class);
    }

    /**
     * Scope pour la sélection FEFO (Lots actifs, non expirés, avec stock > 0, triés par date de péremption la plus proche).
     */
    public function scopeDisponiblesFefo(Builder $query): Builder
    {
        return $query->where('statut', StatutLot::Actif)
            ->where('quantite_actuelle', '>', 0)
            ->where('date_expiration', '>', now())
            ->orderBy('date_expiration', 'asc');
    }

    /**
     * Vérifie si le lot expire bientôt (moins de N jours).
     */
    public function expireBientot(int $jours = 90): bool
    {
        return $this->date_expiration->isFuture()
            && $this->date_expiration->diffInDays(now()) <= $jours;
    }

    /**
     * Vérifie si le lot est périmé.
     */
    public function estPerime(): bool
    {
        return $this->date_expiration->isPast();
    }
}
