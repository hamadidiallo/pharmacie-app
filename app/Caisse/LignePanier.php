<?php

namespace App\Caisse;

use App\Models\Medicament;

/**
 * Une ligne de panier : un médicament et une quantité.
 * Le prix vient toujours du médicament, jamais du client.
 */
final class LignePanier
{
    public function __construct(
        public readonly Medicament $medicament,
        public readonly int $quantite,
    ) {
    }

    public function prix(): float
    {
        return (float) $this->medicament->prix;
    }

    public function sousTotal(): float
    {
        return $this->prix() * $this->quantite;
    }

    public function stockSuffisant(): bool
    {
        return $this->medicament->stock >= $this->quantite;
    }

    /** Attributs de la table pivot medicament__vente. */
    public function attributsPivot(): array
    {
        return [
            'quantite' => $this->quantite,
            'prix' => $this->prix(),
            'sous_total' => $this->sousTotal(),
        ];
    }

    /** Forme consommée par l'écran de vente en JavaScript. */
    public function pourLeNavigateur(): array
    {
        return [
            'id' => $this->medicament->id,
            'nom' => $this->medicament->nom,
            'prix' => $this->prix(),
            'stock' => $this->medicament->stock,
            'quantite' => $this->quantite,
        ];
    }
}
