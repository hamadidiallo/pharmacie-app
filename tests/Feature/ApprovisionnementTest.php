<?php

use App\Enums\Role;
use App\Enums\StatutCommandeFournisseur;
use App\Enums\StatutLot;
use App\Models\CommandeFournisseur;
use App\Models\Fournisseur;
use App\Models\Medicament;
use App\Models\User;
use App\Services\Approvisionnement\GenerateurCommandeAutomatique;
use App\Services\Approvisionnement\ReceptionCommandeAction;

test('un pharmacien peut créer et modifier un fournisseur grossiste', function () {
    $pharmacien = User::factory()->create(['role' => Role::Pharmacien]);

    $reponse = $this->actingAs($pharmacien)->post(route('fournisseurs.store'), [
        'nom' => 'LABOREX MALI',
        'code_fournisseur' => 'LAB-01',
        'telephone' => '+223 20 22 10 10',
        'email' => 'commandes@laborex-mali.com',
        'adresse' => 'Zone Industrielle Sotuba',
        'ville' => 'Bamako',
        'delai_livraison_jours' => 1,
        'conditions_paiement' => '30 jours fin de mois',
    ]);

    $reponse->assertRedirect(route('fournisseurs.index'));

    $fournisseur = Fournisseur::firstWhere('code_fournisseur', 'LAB-01');
    expect($fournisseur)->not->toBeNull()
        ->and($fournisseur->nom)->toBe('LABOREX MALI')
        ->and($fournisseur->delai_livraison_jours)->toBe(1);

    // Modification
    $this->actingAs($pharmacien)->put(route('fournisseurs.update', $fournisseur), [
        'nom' => 'LABOREX MALI SA',
        'code_fournisseur' => 'LAB-01',
        'delai_livraison_jours' => 2,
        'conditions_paiement' => 'Comptant',
        'actif' => 1,
    ])->assertRedirect(route('fournisseurs.index'));

    $fournisseur->refresh();
    expect($fournisseur->nom)->toBe('LABOREX MALI SA')
        ->and($fournisseur->delai_livraison_jours)->toBe(2);
});

test('un bon de commande manuel est enregistré avec ses lignes d articles', function () {
    $pharmacien = User::factory()->create(['role' => Role::Pharmacien]);
    $fournisseur = Fournisseur::factory()->create(['nom' => 'COPHARM']);

    $med1 = Medicament::factory()->create(['nom' => 'Paracétamol 500mg', 'prix' => 1000]);
    $med2 = Medicament::factory()->create(['nom' => 'Amoxicilline 500mg', 'prix' => 2500]);

    $reponse = $this->actingAs($pharmacien)->post(route('commandes.store'), [
        'fournisseur_id' => $fournisseur->id,
        'date_commande' => now()->format('Y-m-d'),
        'date_livraison_prevue' => now()->addDays(2)->format('Y-m-d'),
        'notes' => 'Commande hebdomadaire urgente',
        'lignes' => [
            [
                'medicament_id' => $med1->id,
                'quantite_commandee' => 50,
                'prix_achat_unitaire_estime' => 600,
            ],
            [
                'medicament_id' => $med2->id,
                'quantite_commandee' => 30,
                'prix_achat_unitaire_estime' => 1500,
            ],
        ],
    ]);

    $reponse->assertRedirect();

    $commande = CommandeFournisseur::firstWhere('fournisseur_id', $fournisseur->id);
    expect($commande)->not->toBeNull()
        ->and($commande->statut)->toBe(StatutCommandeFournisseur::Brouillon)
        ->and($commande->lignes()->count())->toBe(2)
        ->and((float) $commande->total_estime)->toBe((float) (50 * 600 + 30 * 1500)); // 30 000 + 45 000 = 75 000
});

test('le générateur automatique suggère les réassorts selon les seuils de sécurité', function () {
    $pharmacien = User::factory()->create(['role' => Role::Pharmacien]);
    $fournisseur = Fournisseur::factory()->create(['nom' => 'UBIPHARM']);

    // Médicament 1 : en rupture (stock 0, seuil 15)
    $medRupture = Medicament::factory()->create([
        'nom' => 'Antibiotique Urgent',
        'stock' => 0,
        'stock_securite' => 15,
        'prix' => 3000,
    ]);

    // Médicament 2 : stock suffisant (stock 100, seuil 10) -> ne doit PAS être suggéré
    Medicament::factory()->create([
        'nom' => 'Produit Saturé',
        'stock' => 100,
        'stock_securite' => 10,
        'prix' => 2000,
    ]);

    $generateur = app(GenerateurCommandeAutomatique::class);
    $besoins = $generateur->detecterBesoinsReassort();

    expect($besoins->contains(fn ($b) => $b['medicament']->id === $medRupture->id))->toBeTrue();

    // Génération automatique du bon de commande
    $commande = $generateur->genererBonCommande($fournisseur, $pharmacien);
    expect($commande)->not->toBeNull()
        ->and($commande->lignes()->count())->toBeGreaterThanOrEqual(1)
        ->and($commande->statut)->toBe(StatutCommandeFournisseur::Brouillon);
});

test('la réception d un bon de livraison crée les lots FEFO et incrémente le stock vendable', function () {
    $pharmacien = User::factory()->create(['role' => Role::Pharmacien]);
    $fournisseur = Fournisseur::factory()->create(['nom' => 'LABOREX']);

    $medicament = Medicament::factory()->create([
        'nom' => 'Insuline NPH',
        'stock' => 5,
        'prix' => 6000,
        'date_expiration' => now()->addMonths(6),
    ]);

    $commande = CommandeFournisseur::create([
        'fournisseur_id' => $fournisseur->id,
        'user_id' => $pharmacien->id,
        'reference' => 'BC-TEST-001',
        'date_commande' => now(),
        'statut' => StatutCommandeFournisseur::Envoyee,
        'total_estime' => 100000,
    ]);

    $ligne = $commande->lignes()->create([
        'medicament_id' => $medicament->id,
        'quantite_commandee' => 25,
        'quantite_recue' => 0,
        'prix_achat_unitaire_estime' => 4000,
    ]);

    // Réception du Bon de Livraison (BL)
    $actionReception = app(ReceptionCommandeAction::class);
    $dateExpLot = now()->addYears(2)->format('Y-m-d');

    $commandeRecue = $actionReception->execute($commande, 'BL-LAB-8912', [
        [
            'ligne_id' => $ligne->id,
            'quantite_recue' => 25,
            'numero_lot' => 'LOT-INS-2026-B8',
            'date_expiration' => $dateExpLot,
            'prix_achat_facture' => 3950,
        ],
    ], 98750);

    $ligne->refresh();
    $medicament->refresh();

    // Vérifications sur la commande et la ligne
    expect($commandeRecue->statut)->toBe(StatutCommandeFournisseur::Recue)
        ->and($commandeRecue->numero_bl)->toBe('BL-LAB-8912')
        ->and($ligne->quantite_recue)->toBe(25)
        ->and($ligne->resteALivrer())->toBe(0)
        ->and($ligne->medicament_lot_id)->not->toBeNull();

    // Vérification du stock vendable : 5 initial + 25 reçus = 30
    expect($medicament->stock)->toBe(30);

    // Vérification du lot FEFO créé
    $lotCree = $ligne->lot;
    expect($lotCree)->not->toBeNull()
        ->and($lotCree->numero_lot)->toBe('LOT-INS-2026-B8')
        ->and($lotCree->quantite_initiale)->toBe(25)
        ->and($lotCree->quantite_actuelle)->toBe(25)
        ->and($lotCree->statut)->toBe(StatutLot::Actif)
        ->and((float) $lotCree->prix_achat_unitaire)->toBe(3950.0);
});

test('l impression du bon de commande officiel et les écrans de réception répondent avec succès', function () {
    $pharmacien = User::factory()->create(['role' => Role::Pharmacien]);
    $fournisseur = Fournisseur::factory()->create(['nom' => 'COPHARM MALI']);

    $commande = CommandeFournisseur::create([
        'fournisseur_id' => $fournisseur->id,
        'user_id' => $pharmacien->id,
        'reference' => 'BC-PRINT-001',
        'date_commande' => now(),
        'statut' => StatutCommandeFournisseur::Envoyee,
        'total_estime' => 50000,
    ]);

    $this->actingAs($pharmacien)->get(route('commandes.show', $commande))
        ->assertOk()
        ->assertSee('BC-PRINT-001');

    $this->actingAs($pharmacien)->get(route('commandes.bon-commande', $commande))
        ->assertOk()
        ->assertSee('BON DE COMMANDE')
        ->assertSee('COPHARM MALI');

    $this->actingAs($pharmacien)->get(route('commandes.reception', $commande))
        ->assertOk()
        ->assertSee('Réception Marchandises');
});
