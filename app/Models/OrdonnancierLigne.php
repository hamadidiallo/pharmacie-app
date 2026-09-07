<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdonnancierLigne extends Model
{
    use HasFactory;

    protected $table = 'ordonnancier_lignes';

    protected $fillable = [
        'numero_ordonnancier',
        'vente_id',
        'medicament_id',
        'medicament_lot_id',
        'date_prescription',
        'date_delivrance',
        'nom_prescripteur',
        'specialite_prescripteur',
        'nom_patient',
        'age_patient',
        'posologie',
        'quantite_delivree',
        'pharmacien_id',
        'notes',
    ];

    protected $casts = [
        'date_prescription' => 'date',
        'date_delivrance' => 'datetime',
        'age_patient' => 'integer',
        'quantite_delivree' => 'integer',
    ];

    public function vente(): BelongsTo
    {
        return $this->belongsTo(Vente::class);
    }

    public function medicament(): BelongsTo
    {
        return $this->belongsTo(Medicament::class)->withTrashed();
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(MedicamentLot::class, 'medicament_lot_id');
    }

    public function pharmacien(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pharmacien_id');
    }

    public static function genererNumero(): string
    {
        $prefix = 'ORD-' . date('Y') . '-';
        $dernier = self::where('numero_ordonnancier', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('numero_ordonnancier');

        $sequence = 1;
        if ($dernier && preg_match('/-(\d+)$/', $dernier, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return $prefix . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
}
