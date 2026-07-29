<?php

namespace App\Enums;

/**
 * État du stock d'un médicament. Porte à la fois le libellé et l'habillage,
 * pour que la règle ne soit écrite qu'une fois.
 */
enum StatutStock: string
{
    case Rupture = 'rupture';
    case Faible = 'faible';
    case Ok = 'ok';

    public function libelle(): string
    {
        return match ($this) {
            self::Rupture => 'Rupture',
            self::Faible => 'Stock faible',
            self::Ok => 'En stock',
        };
    }

    /** Classe de pastille définie dans resources/css/app.css. */
    public function classePastille(): string
    {
        return match ($this) {
            self::Rupture => 'pill-danger',
            self::Faible => 'pill-warn',
            self::Ok => 'pill-ok',
        };
    }

    /** Quantité annotée, telle qu'affichée dans la liste des médicaments. */
    public function quantiteAnnotee(int $stock): string
    {
        return match ($this) {
            self::Rupture => '0 · rupture',
            self::Faible => $stock . ' · faible',
            self::Ok => (string) $stock,
        };
    }
}
