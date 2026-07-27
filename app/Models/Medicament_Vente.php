<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Medicament_Vente extends Pivot
{
    protected $fillable = [
        'medicament_id',
        'vente_id',
        'quantite',
        'prix',
        'sous_total'
    ];
    public function medicament() : BelongsTo {
        return $this->belongsTo(Medicament::class);
    }
    public function vente() : BelongsTo {
        return $this->belongsTo(Vente::class);
    }

}
