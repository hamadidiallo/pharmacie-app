<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vente extends Model
{
    protected $fillable = [
        'total',
        'date_vente',
        'user_id'
    ];
    protected $casts = [
        'date_vente' => 'datetime',
    ];
    public function medicaments()
    {
        return $this->belongsToMany(
            Medicament::class,
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
}
