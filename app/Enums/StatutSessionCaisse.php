<?php

namespace App\Enums;

enum StatutSessionCaisse: string
{
    case Ouverte = 'ouverte';
    case Cloturee = 'cloturee';

    public function libelle(): string
    {
        return match ($this) {
            self::Ouverte => 'Caisse Ouverte',
            self::Cloturee => 'Clôturée (Rapport Z)',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Ouverte => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
            self::Cloturee => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
        };
    }
}
