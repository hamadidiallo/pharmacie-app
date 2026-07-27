<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicament extends Model
{
    protected $fillable = [
        'nom',
        'prix',
        'stock',
        'date_expiration',
        'description',
    ];
    protected $casts = [
        'date_expiration' => 'date',
    ];
    public function ventes()
    {
        return $this->belongsToMany(
            Vente::class,
            'medicament__vente'
        )
            ->withPivot('quantite', 'prix', 'sous_total')
            ->withTimestamps();
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
