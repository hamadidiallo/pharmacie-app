<?php

namespace App\Enums;

enum StatutLot: string
{
    case Actif = 'actif';
    case Isole = 'isole';
    case Rappele = 'rappele';
    case Epuise = 'epuise';

    public function libelle(): string
    {
        return match ($this) {
            self::Actif => 'Actif',
            self::Isole => 'Isolé (Quarantaine)',
            self::Rappele => 'Rappelé (ANRP)',
            self::Epuise => 'Épuisé',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Actif => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
            self::Isole => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20',
            self::Rappele => 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20',
            self::Epuise => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
        };
    }

    public function estDisponibleVente(): bool
    {
        return $this === self::Actif;
    }
}
