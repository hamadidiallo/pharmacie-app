<?php

namespace App\Enums;

enum StatutBordereauAssurance: string
{
    case Brouillon = 'Brouillon';
    case Transmis = 'Transmis';
    case Regle = 'Regle';
    case Rejete = 'Rejete';

    public function label(): string
    {
        return match ($this) {
            self::Brouillon => 'Brouillon',
            self::Transmis => 'Transmis à l\'assurance',
            self::Regle => 'Réglé / Encaissé',
            self::Rejete => 'Rejeté / Litige',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Brouillon => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 border-slate-300',
            self::Transmis => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-300',
            self::Regle => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-300',
            self::Rejete => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border-rose-300',
        };
    }
}
