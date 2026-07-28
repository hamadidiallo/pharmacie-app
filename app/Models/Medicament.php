<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicament extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prix',
        'stock',
        'date_expiration',
        'description',
        'user_id',
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
            ->using(Medicament_Vente::class)
            ->withPivot('quantite', 'prix', 'sous_total')
            ->withTimestamps();
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
