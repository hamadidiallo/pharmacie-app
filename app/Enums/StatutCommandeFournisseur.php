<?php

namespace App\Enums;

enum StatutCommandeFournisseur: string
{
    case Brouillon = 'brouillon';
    case Envoyee = 'envoyee';
    case PartiellementRecue = 'partiellement_recue';
    case Recue = 'recue';
    case Annulee = 'annulee';

    public function libelle(): string
    {
        return match ($this) {
            self::Brouillon => 'Brouillon',
            self::Envoyee => 'Envoyée au grossiste',
            self::PartiellementRecue => 'Partiellement reçue',
            self::Recue => 'Réceptionnée (Soldée)',
            self::Annulee => 'Annulée',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Brouillon => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
            self::Envoyee => 'bg-sky-50 text-sky-700 ring-1 ring-sky-600/20',
            self::PartiellementRecue => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20',
            self::Recue => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
            self::Annulee => 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20',
        };
    }
}
