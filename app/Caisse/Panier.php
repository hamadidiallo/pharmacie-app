<?php

namespace App\Caisse;

use App\Exceptions\VenteException;
use App\Models\Medicament;

/**
 * Panier en cours d'encaissement.
 *
 * Porte les deux règles qui protègent la caisse : le prix est toujours relu
 * en base, et le stock est contrôlé avant toute écriture.
 */
final class Panier
{
    /** @param  array<int,LignePanier>  $lignes  indexé par identifiant de médicament */
    private function __construct(private readonly array $lignes)
    {
    }

    /**
     * Construit le panier depuis des quantités, en relisant les médicaments.
     *
     * @param  array<int,int>  $quantites  [medicament_id => quantite]
     *
     * @throws VenteException si un médicament est introuvable ou archivé
     */
    public static function depuisQuantites(array $quantites, bool $verrouiller = false): self
    {
        if ($quantites === []) {
            return new self([]);
        }

        $requete = Medicament::whereIn('id', array_keys($quantites));

        if ($verrouiller) {
            // empêche deux encaissements simultanés de consommer le même stock
            $requete->lockForUpdate();
        }

        $medicaments = $requete->get()->keyBy('id');

        $lignes = [];

        foreach ($quantites as $id => $quantite) {

            $medicament = $medicaments->get($id);

            if (!$medicament) {
                throw new VenteException('Médicament introuvable', 404);
            }

            $lignes[$medicament->id] = new LignePanier($medicament, (int) $quantite);
        }

        return new self($lignes);
    }

    /**
     * Construit le panier depuis des lignes déjà résolues.
     * Utilisé par les tests, sans accès à la base.
     *
     * @param  array<int,LignePanier>  $lignes
     */
    public static function avec(array $lignes): self
    {
        $indexees = [];

        foreach ($lignes as $ligne) {
            $indexees[$ligne->medicament->id ?? count($indexees)] = $ligne;
        }

        return new self($indexees);
    }

    /**
     * Normalise le panier brut envoyé par le navigateur en quantités sûres.
     * Un même produit envoyé deux fois voit ses quantités cumulées.
     *
     * @return array<int,int>
     *
     * @throws VenteException si le panier est vide ou mal formé
     */
    public static function normaliser(mixed $panierBrut): array
    {
        if (!is_array($panierBrut) || $panierBrut === []) {
            throw new VenteException('Le panier est vide', 422);
        }

        $quantites = [];

        foreach ($panierBrut as $item) {

            if (!is_array($item) || !isset($item['id'], $item['quantite']) || (int) $item['quantite'] < 1) {
                throw new VenteException('Données panier incorrectes', 422);
            }

            $id = (int) $item['id'];

            $quantites[$id] = ($quantites[$id] ?? 0) + (int) $item['quantite'];
        }

        return $quantites;
    }

    /** @return array<int,LignePanier> */
    public function lignes(): array
    {
        return $this->lignes;
    }

    public function estVide(): bool
    {
        return $this->lignes === [];
    }

    public function nombreArticles(): int
    {
        return array_sum(array_map(fn (LignePanier $ligne) => $ligne->quantite, $this->lignes));
    }

    public function total(): float
    {
        return array_sum(array_map(fn (LignePanier $ligne) => $ligne->sousTotal(), $this->lignes));
    }

    /** @throws VenteException au premier produit dont le stock est insuffisant */
    public function controlerStock(): void
    {
        foreach ($this->lignes as $ligne) {
            if (!$ligne->stockSuffisant()) {
                throw new VenteException('Stock insuffisant pour ' . $ligne->medicament->nom, 422);
            }
        }
    }

    /** @return array<int,array<string,mixed>> prêt pour attach() */
    public function attributsPivot(): array
    {
        return array_map(fn (LignePanier $ligne) => $ligne->attributsPivot(), $this->lignes);
    }

    /** @return array<int,array<string,mixed>> prêt pour @json en Blade */
    public function pourLeNavigateur(): array
    {
        return array_values(array_map(fn (LignePanier $ligne) => $ligne->pourLeNavigateur(), $this->lignes));
    }

    public function contientMedicamentSousOrdonnance(): bool
    {
        foreach ($this->lignes as $ligne) {
            $m = $ligne->medicament;
            if ($m->ordonnance_requise || ($m->tableau instanceof \App\Enums\TableauReglementaire && $m->tableau->requiertOrdonnance())) {
                return true;
            }
        }
        return false;
    }

    /** @return array<int,LignePanier> */
    public function lignesSousOrdonnance(): array
    {
        return array_filter($this->lignes, function (LignePanier $ligne) {
            $m = $ligne->medicament;
            return $m->ordonnance_requise || ($m->tableau instanceof \App\Enums\TableauReglementaire && $m->tableau->requiertOrdonnance());
        });
    }
}
