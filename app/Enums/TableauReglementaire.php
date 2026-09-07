<?php

namespace App\Enums;

enum TableauReglementaire: string
{
    case NonListe = 'non_liste';
    case Liste1 = 'liste_1';
    case Liste2 = 'liste_2';
    case Stupefiant = 'stupefiant';

    public function libelle(): string
    {
        return match ($this) {
            self::NonListe => 'Hors Liste (Vente libre)',
            self::Liste1 => 'Liste I (Cadre rouge)',
            self::Liste2 => 'Liste II (Cadre vert)',
            self::Stupefiant => 'Stupéfiant (Contrôle strict)',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::NonListe => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
            self::Liste1 => 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20',
            self::Liste2 => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
            self::Stupefiant => 'bg-purple-50 text-purple-700 ring-1 ring-purple-600/20 font-bold',
        };
    }

    public function requiertOrdonnance(): bool
    {
        return $this !== self::NonListe;
    }
}
