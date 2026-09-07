<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'firstname',
        'lastname',
        'username',
        'role',
        'email',
        'telephone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class);
    }

    public function medicaments()
    {
        return $this->hasMany(Medicament::class);
    }

    // ---- Méthodes de vérification des rôles (RBAC) ----

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isPharmacien(): bool
    {
        return $this->role === Role::Pharmacien;
    }

    public function isCaissier(): bool
    {
        return $this->role === Role::Caissier;
    }

    /**
     * Vérifie si l'utilisateur possède l'un des rôles indiqués.
     */
    public function hasRole(Role|string ...$roles): bool
    {
        foreach ($roles as $role) {
            $valeur = $role instanceof Role ? $role->value : $role;
            if ($this->role?->value === $valeur) {
                return true;
            }
        }

        return false;
    }
}
