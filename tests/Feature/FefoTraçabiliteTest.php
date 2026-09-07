<?php

use App\Enums\Role;
use App\Enums\StatutLot;
use App\Enums\TableauReglementaire;
use App\Models\Medicament;
use App\Models\MedicamentLot;
use App\Models\User;
use App\Models\Vente;
use App\Services\Caisse\AnnulerVenteAction;
use App\Services\Caisse\EnregistrerVenteAction;

test('la création d un médicament initialise son premier lot de traçabilité FEFO', function () {
    $pharmacien = User::factory()->create(['role' => Role::Pharmacien]);

    $response = $this->actingAs($pharmacien)->post(route('medicaments.store'), [
        'nom' => 'Augmentin 1g',
        'dci' => 'Amoxicilline + Acide Clavulanique',
        'code_barre' => '3400936192131',
        'forme' => 'Comprimé',
        'dosage' => '1g',
        'tableau' => TableauReglementaire::Liste1->value,
        'ordonnance_requise' => 1,
        'prix' => 4500,
        'stock' => 30,
        'numero_lot' => 'AUG-2026-LOT1',
        'date_expiration' => now()->addMonths(18)->format('Y-m-d'),
        'description' => 'Antibiotique à large spectre',
    ]);

    $response->assertRedirect(route('medicaments.index'));

    $medicament = Medicament::firstWhere('nom', 'Augmentin 1g');
    expect($medicament)->not->toBeNull()
        ->and($medicament->dci)->toBe('Amoxicilline + Acide Clavulanique')
        ->and($medicament->code_barre)->toBe('3400936192131')
        ->and($medicament->tableau)->toBe(TableauReglementaire::Liste1)
        ->and($medicament->ordonnance_requise)->toBeTrue()
        ->and($medicament->lots()->count())->toBe(1);

    $lot = $medicament->lots->first();
    expect($lot->numero_lot)->toBe('AUG-2026-LOT1')
        ->and($lot->quantite_initiale)->toBe(30)
        ->and($lot->quantite_actuelle)->toBe(30)
        ->and($lot->statut)->toBe(StatutLot::Actif);
});

test('l algorithme FEFO déstocke en priorité le lot qui expire le plus tôt', function () {
    $caissier = User::factory()->create(['role' => Role::Caissier]);

    $medicament = Medicament::factory()->create([
        'nom' => 'Doliprane 1000mg',
        'prix' => 1000,
        'stock' => 25,
        'date_expiration' => now()->addMonths(6),
    ]);

    // Lot 1 : Expire dans 3 mois (10 boîtes) -> doit sortir en PREMIER
    $lotUrgent = $medicament->lots()->create([
        'numero_lot' => 'LOT-EXP-COURTE',
        'quantite_initiale' => 10,
        'quantite_actuelle' => 10,
        'date_expiration' => now()->addMonths(3),
        'statut' => StatutLot::Actif,
    ]);

    // Lot 2 : Expire dans 12 mois (15 boîtes) -> sort en SECOND
    $lotLong = $medicament->lots()->create([
        'numero_lot' => 'LOT-EXP-LONGUE',
        'quantite_initiale' => 15,
        'quantite_actuelle' => 15,
        'date_expiration' => now()->addMonths(12),
        'statut' => StatutLot::Actif,
    ]);

    // Vente de 14 boîtes : prend 10 dans Lot 1 et 4 dans Lot 2
    $actionVente = app(EnregistrerVenteAction::class);
    $actionVente->execute([
        $medicament->id => 14,
    ], 'especes', 15000, $caissier->id);

    $lotUrgent->refresh();
    $lotLong->refresh();
    $medicament->refresh();

    expect($lotUrgent->quantite_actuelle)->toBe(0)
        ->and($lotUrgent->statut)->toBe(StatutLot::Epuise)
        ->and($lotLong->quantite_actuelle)->toBe(11) // 15 - 4
        ->and($lotLong->statut)->toBe(StatutLot::Actif)
        ->and($medicament->stock)->toBe(11);
});

test('un lot isolé pour rappel sanitaire est exclu des ventes et du stock vendable', function () {
    $pharmacien = User::factory()->create(['role' => Role::Pharmacien]);

    $medicament = Medicament::factory()->create([
        'nom' => 'Sirop Antitussif',
        'prix' => 2000,
        'stock' => 50,
        'date_expiration' => now()->addYear(),
    ]);

    $lotSuspect = $medicament->lots()->create([
        'numero_lot' => 'LOT-CONTAMINE',
        'quantite_initiale' => 20,
        'quantite_actuelle' => 20,
        'date_expiration' => now()->addMonths(6),
        'statut' => StatutLot::Actif,
    ]);

    $medicament->lots()->create([
        'numero_lot' => 'LOT-SAIN',
        'quantite_initiale' => 30,
        'quantite_actuelle' => 30,
        'date_expiration' => now()->addMonths(12),
        'statut' => StatutLot::Actif,
    ]);

    // Mise en quarantaine par le pharmacien
    $this->actingAs($pharmacien)->post(route('medicaments.lots.isoler', $lotSuspect), [
        'motif' => 'Alerte ANRP 2026/01 contamination',
        'statut' => 'rappele',
    ])->assertRedirect();

    $lotSuspect->refresh();
    $medicament->refresh();

    expect($lotSuspect->statut)->toBe(StatutLot::Rappele)
        ->and($lotSuspect->motif_isolement)->toBe('Alerte ANRP 2026/01 contamination')
        ->and($medicament->stock)->toBe(30); // Seul le lot sain reste vendable !

    // Réactivation
    $this->actingAs($pharmacien)->post(route('medicaments.lots.reactiver', $lotSuspect))
        ->assertRedirect();

    $lotSuspect->refresh();
    $medicament->refresh();

    expect($lotSuspect->statut)->toBe(StatutLot::Actif)
        ->and($medicament->stock)->toBe(50);
});

test('l annulation d une vente restitue les articles dans leurs lots d origine respectifs', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $medicament = Medicament::factory()->create([
        'nom' => 'Vitamine C 1000',
        'prix' => 1000,
        'stock' => 15,
        'date_expiration' => now()->addYear(),
    ]);

    $lotA = $medicament->lots()->create([
        'numero_lot' => 'LOT-A',
        'quantite_initiale' => 5,
        'quantite_actuelle' => 5,
        'date_expiration' => now()->addMonths(2),
        'statut' => StatutLot::Actif,
    ]);

    $lotB = $medicament->lots()->create([
        'numero_lot' => 'LOT-B',
        'quantite_initiale' => 10,
        'quantite_actuelle' => 10,
        'date_expiration' => now()->addMonths(8),
        'statut' => StatutLot::Actif,
    ]);

    // Vente de 7 boîtes (5 dans Lot A, 2 dans Lot B)
    $actionVente = app(EnregistrerVenteAction::class);
    $vente = $actionVente->execute([
        $medicament->id => 7,
    ], 'especes', 7000, $admin->id);

    $lotA->refresh();
    $lotB->refresh();
    expect($lotA->quantite_actuelle)->toBe(0)
        ->and($lotA->statut)->toBe(StatutLot::Epuise)
        ->and($lotB->quantite_actuelle)->toBe(8);

    // Annulation de la vente par l'administrateur
    $actionAnnuler = app(AnnulerVenteAction::class);
    $actionAnnuler->execute($vente);

    $lotA->refresh();
    $lotB->refresh();
    $medicament->refresh();

    expect($lotA->quantite_actuelle)->toBe(5)
        ->and($lotA->statut)->toBe(StatutLot::Actif) // Réactivé car du stock est revenu !
        ->and($lotB->quantite_actuelle)->toBe(10)
        ->and($medicament->stock)->toBe(15);
});

test('la recherche par DCI et par code-barres retourne le médicament correspondant', function () {
    $caissier = User::factory()->create(['role' => Role::Caissier]);

    $medicament = Medicament::factory()->create([
        'nom' => 'Efferalgan 500mg',
        'dci' => 'Paracétamol',
        'code_barre' => '3400931490218',
        'stock' => 40,
        'date_expiration' => now()->addMonths(12),
    ]);

    // Recherche par DCI
    $reponseDci = $this->actingAs($caissier)->getJson(route('medicaments.search', ['q' => 'Paracétamol']));
    $reponseDci->assertOk()
        ->assertJsonFragment(['nom' => 'Efferalgan 500mg']);

    // Recherche par code-barres douchette
    $reponseCode = $this->actingAs($caissier)->getJson(route('medicaments.search', ['q' => '3400931490218']));
    $reponseCode->assertOk()
        ->assertJsonFragment(['id' => $medicament->id, 'code_barre' => '3400931490218']);
});
