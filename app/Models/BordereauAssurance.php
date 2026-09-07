<?php

namespace App\Models;

use App\Enums\StatutBordereauAssurance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BordereauAssurance extends Model
{
    use HasFactory;

    protected $table = 'bordereaux_assurance';

    protected $fillable = [
        'reference',
        'assurance_id',
        'periode_debut',
        'periode_fin',
        'montant_total',
        'nombre_dossiers',
        'statut',
        'date_transmission',
        'date_reglement',
        'mode_reglement',
        'reference_reglement',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'date_transmission' => 'datetime',
        'date_reglement' => 'datetime',
        'montant_total' => 'float',
        'nombre_dossiers' => 'integer',
        'statut' => StatutBordereauAssurance::class,
    ];

    public function assurance(): BelongsTo
    {
        return $this->belongsTo(Assurance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ventes(): HasMany
    {
        return $this->hasMany(Vente::class, 'bordereau_assurance_id');
    }

    public static function genererReference(Assurance $assurance): string
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $assurance->code ?? 'ASSUR'));
        $prefix = "BORD-{$code}-" . date('Ym') . "-";
        $dernier = self::where('reference', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('reference');

        $sequence = 1;
        if ($dernier && preg_match('/-(\d+)$/', $dernier, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function recalculer(): void
    {
        $this->montant_total = (float) $this->ventes()->sum('part_assurance');
        $this->nombre_dossiers = $this->ventes()->count();
        $this->save();
    }

    public function marquerTransmis(): void
    {
        $this->update([
            'statut' => StatutBordereauAssurance::Transmis,
            'date_transmission' => now(),
        ]);

        $this->ventes()->update(['statut_remboursement' => 'transmis']);
    }

    public function enregistrerReglement(?string $mode = null, ?string $ref = null, ?string $notes = null): void
    {
        $this->update([
            'statut' => StatutBordereauAssurance::Regle,
            'date_reglement' => now(),
            'mode_reglement' => $mode ?? $this->mode_reglement,
            'reference_reglement' => $ref ?? $this->reference_reglement,
            'notes' => $notes ?? $this->notes,
        ]);

        $this->ventes()->update(['statut_remboursement' => 'rembourse']);
    }
}
