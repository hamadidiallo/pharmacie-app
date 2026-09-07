<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Pharmacien = 'pharmacien';
    case Caissier = 'caissier';

    public function libelle(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Pharmacien => 'Pharmacien',
            self::Caissier => 'Caissier',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Admin => 'bg-purple-100 text-purple-800',
            self::Pharmacien => 'bg-leaf/15 text-leaf',
            self::Caissier => 'bg-blue-100 text-blue-800',
        };
    }
}
