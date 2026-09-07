<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementCaisse extends Model
{
    use HasFactory;

    protected $table = 'mouvements_caisse';

    protected $fillable = [
        'session_caisse_id',
        'user_id',
        'type', // 'sortie' (dépense) ou 'entree' (apport)
        'montant',
        'motif',
        'beneficiaire',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(SessionCaisse::class, 'session_caisse_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function estSortie(): bool
    {
        return $this->type === 'sortie';
    }

    public function estEntree(): bool
    {
        return $this->type === 'entree';
    }
}
