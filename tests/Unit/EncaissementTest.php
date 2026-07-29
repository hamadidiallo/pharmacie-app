<?php

use App\Caisse\Encaissement;
use App\Exceptions\MontantInsuffisantException;

it('calcule la monnaie à rendre en espèces', function () {
    $encaissement = Encaissement::pour('especes', 3650, 5000);

    expect($encaissement->mode)->toBe('especes')
        ->and($encaissement->montantRecu)->toBe(5000.0)
        ->and($encaissement->monnaieRendue)->toBe(1350.0);
});

it('ne rend rien quand le client paie au centime', function () {
    expect(Encaissement::pour('especes', 3650, 3650)->monnaieRendue)->toBe(0.0);
});

it('refuse un montant reçu inférieur au total', function () {
    expect(fn () => Encaissement::pour('especes', 3650, 3000))
        ->toThrow(MontantInsuffisantException::class, 'inférieur au total');
});

it('refuse les espèces sans montant reçu', function () {
    expect(fn () => Encaissement::pour('especes', 3650, null))
        ->toThrow(MontantInsuffisantException::class);
});

it('désigne le champ fautif pour que le formulaire l\'affiche', function () {
    expect(MontantInsuffisantException::CHAMP)->toBe('montant_recu');
});

it('ne demande ni montant reçu ni monnaie hors espèces', function (string $mode) {
    $encaissement = Encaissement::pour($mode, 3650);

    expect($encaissement->mode)->toBe($mode)
        ->and($encaissement->montantRecu)->toBeNull()
        ->and($encaissement->monnaieRendue)->toBeNull();
})->with(['mobile_money', 'carte']);

it('ignore un montant reçu envoyé par erreur hors espèces', function () {
    // le formulaire masque le champ, mais rien n'empêche de le poster
    $encaissement = Encaissement::pour('carte', 3650, 99999);

    expect($encaissement->montantRecu)->toBeNull()
        ->and($encaissement->monnaieRendue)->toBeNull();
});

it('expose les attributs à enregistrer sur la vente', function () {
    expect(Encaissement::pour('especes', 1000, 2000)->attributs())->toBe([
        'mode_paiement' => 'especes',
        'montant_recu' => 2000.0,
        'monnaie_rendue' => 1000.0,
    ]);
});
