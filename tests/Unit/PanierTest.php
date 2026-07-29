<?php

use App\Caisse\LignePanier;
use App\Caisse\Panier;
use App\Exceptions\VenteException;
use App\Models\Medicament;

/** Médicament en mémoire : ces tests ne touchent pas la base. */
function medicament(string $nom, float $prix, int $stock, int $id = 1): Medicament
{
    $medicament = new Medicament(['nom' => $nom, 'prix' => $prix, 'stock' => $stock]);
    $medicament->id = $id;

    return $medicament;
}

describe('LignePanier', function () {

    it('calcule le sous-total au prix du médicament', function () {
        $ligne = new LignePanier(medicament('Paracétamol', 250, 100), 3);

        expect($ligne->prix())->toBe(250.0)
            ->and($ligne->sousTotal())->toBe(750.0);
    });

    it('ignore tout prix venu d\'ailleurs que du médicament', function () {
        // il n'existe aucun moyen d'injecter un prix : le constructeur ne le prend pas
        $ligne = new LignePanier(medicament('Amoxicilline', 1200, 10), 2);

        expect($ligne->attributsPivot())->toBe([
            'quantite' => 2,
            'prix' => 1200.0,
            'sous_total' => 2400.0,
        ]);
    });

    it('détecte un stock insuffisant', function () {
        expect((new LignePanier(medicament('X', 100, 5), 5))->stockSuffisant())->toBeTrue()
            ->and((new LignePanier(medicament('X', 100, 5), 6))->stockSuffisant())->toBeFalse();
    });
});

describe('Panier', function () {

    it('additionne les sous-totaux', function () {
        $panier = Panier::avec([
            new LignePanier(medicament('Paracétamol', 250, 100, 1), 2),
            new LignePanier(medicament('Amoxicilline', 1200, 10, 2), 1),
            new LignePanier(medicament('Ibuprofène', 650, 30, 3), 3),
        ]);

        expect($panier->total())->toBe(3650.0)
            ->and($panier->nombreArticles())->toBe(6)
            ->and($panier->estVide())->toBeFalse();
    });

    it('est vide par défaut', function () {
        expect(Panier::avec([])->estVide())->toBeTrue()
            ->and(Panier::avec([])->total())->toBe(0.0)
            ->and(Panier::avec([])->nombreArticles())->toBe(0);
    });

    it('refuse le panier au premier stock insuffisant, en nommant le produit', function () {
        $panier = Panier::avec([
            new LignePanier(medicament('Disponible', 100, 50, 1), 2),
            new LignePanier(medicament('Presque épuisé', 100, 1, 2), 5),
        ]);

        expect(fn () => $panier->controlerStock())
            ->toThrow(VenteException::class, 'Stock insuffisant pour Presque épuisé');
    });

    it('accepte un panier qui consomme exactement le stock restant', function () {
        $panier = Panier::avec([new LignePanier(medicament('Dernier lot', 100, 4, 1), 4)]);

        expect(fn () => $panier->controlerStock())->not->toThrow(VenteException::class);
    });

    it('cumule les quantités d\'un même produit envoyé deux fois', function () {
        $quantites = Panier::normaliser([
            ['id' => 7, 'quantite' => 2],
            ['id' => 9, 'quantite' => 1],
            ['id' => 7, 'quantite' => 3],
        ]);

        expect($quantites)->toBe([7 => 5, 9 => 1]);
    });

    it('convertit les identifiants et quantités en entiers', function () {
        expect(Panier::normaliser([['id' => '7', 'quantite' => '2']]))->toBe([7 => 2]);
    });

    it('rejette un panier vide', function () {
        expect(fn () => Panier::normaliser([]))->toThrow(VenteException::class, 'Le panier est vide');
        expect(fn () => Panier::normaliser(null))->toThrow(VenteException::class, 'Le panier est vide');
    });

    it('rejette une ligne mal formée', function (mixed $ligne) {
        expect(fn () => Panier::normaliser([$ligne]))
            ->toThrow(VenteException::class, 'Données panier incorrectes');
    })->with([
        'quantité nulle' => [['id' => 1, 'quantite' => 0]],
        'quantité négative' => [['id' => 1, 'quantite' => -3]],
        'identifiant absent' => [['quantite' => 1]],
        'quantité absente' => [['id' => 1]],
        'pas un tableau' => ['n\'importe quoi'],
    ]);

    it('produit les attributs de pivot indexés par médicament', function () {
        $panier = Panier::avec([
            new LignePanier(medicament('A', 500, 10, 4), 2),
            new LignePanier(medicament('B', 300, 10, 9), 1),
        ]);

        expect($panier->attributsPivot())->toBe([
            4 => ['quantite' => 2, 'prix' => 500.0, 'sous_total' => 1000.0],
            9 => ['quantite' => 1, 'prix' => 300.0, 'sous_total' => 300.0],
        ]);
    });
});
