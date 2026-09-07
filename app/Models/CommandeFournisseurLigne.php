<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandeFournisseurLigne extends Model
{
    use HasFactory;

    protected $table = 'commande_fournisseur_lignes';

    protected $fillable = [
        'commande_fournisseur_id',
        'medicament_id',
        'quantite_commandee',
        'quantite_recue',
        'prix_achat_unitaire_estime',
        'prix_achat_unitaire_facture',
        'numero_lot_recu',
        'date_expiration_recue',
        'medicament_lot_id',
    ];

    protected function casts(): array
    {
        return [
            'quantite_commandee' => 'integer',
            'quantite_recue' => 'integer',
            'prix_achat_unitaire_estime' => 'decimal:2',
            'prix_achat_unitaire_facture' => 'decimal:2',
            'date_expiration_recue' => 'date',
        ];
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(CommandeFournisseur::class, 'commande_fournisseur_id');
    }

    public function medicament(): BelongsTo
    {
        return $this->belongsTo(Medicament::class, 'medicament_id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(MedicamentLot::class, 'medicament_lot_id');
    }

    public function sousTotalEstime(): float
    {
        return $this->quantite_commandee * (float) $this->prix_achat_unitaire_estime;
    }

    public function resteALivrer(): int
    {
        return max(0, $this->quantite_commandee - $this->quantite_recue);
    }
}
