<?php

use App\Enums\Role;
use App\Enums\StatutSessionCaisse;
use App\Models\Medicament;
use App\Models\SessionCaisse;
use App\Models\User;
use App\Models\Vente;
use App\Services\Caisse\AnnulerVenteAction;
use App\Services\Caisse\EnregistrerVenteAction;
use App\Services\Caisse\SessionCaisseService;

test('un caissier peut ouvrir une session de caisse avec son fond initial', function () {
    $caissier = User::factory()->create(['role' => Role::Caissier]);

    $reponse = $this->actingAs($caissier)->post(route('caisse.sessions.store'), [
        'fond_caisse_ouverture' => 30000,
        'observations' => 'Garde de jour',
    ]);

    $reponse->assertRedirect(route('caisse.sessions.index'));

    $session = SessionCaisse::firstWhere('user_id', $caissier->id);
    expect($session)->not->toBeNull()
        ->and((float) $session->fond_caisse_ouverture)->toBe(30000.0)
        ->and($session->statut)->toBe(StatutSessionCaisse::Ouverte)
        ->and($session->soldeTheoriqueAttendu())->toBe(30000.0);
});

test('les ventes sont automatiquement rattachées à la session de caisse ouverte et incrémentent les totaux', function () {
    $caissier = User::factory()->create(['role' => Role::Caissier]);
    $serviceCaisse = app(SessionCaisseService::class);
    $session = $serviceCaisse->ouvrir($caissier, 25000);

    $medicament = Medicament::factory()->create([
        'prix' => 2000,
        'stock' => 50,
        'date_expiration' => now()->addYear(),
    ]);

    $actionVente = app(EnregistrerVenteAction::class);

    // Vente 1 : 3 unités en Espèces (6 000 FCFA)
    $vente1 = $actionVente->execute([$medicament->id => 3], 'especes', 10000, $caissier->id);

    // Vente 2 : 2 unités en Mobile Money (4 000 FCFA)
    $vente2 = $actionVente->execute([$medicament->id => 2], 'mobile_money', null, $caissier->id);

    $session->refresh();

    expect($vente1->session_caisse_id)->toBe($session->id)
        ->and($vente2->session_caisse_id)->toBe($session->id)
        ->and((float) $session->total_especes_theorique)->toBe(6000.0)
        ->and((float) $session->total_mobile_money)->toBe(4000.0)
        ->and($session->totalChiffreAffaires())->toBe(10000.0)
        ->and($session->soldeTheoriqueAttendu())->toBe(31000.0); // 25 000 fond + 6 000 espèces
});

test('une sortie d espèces pour dépense réduit le solde théorique de la caisse', function () {
    $caissier = User::factory()->create(['role' => Role::Caissier]);
    $serviceCaisse = app(SessionCaisseService::class);
    $session = $serviceCaisse->ouvrir($caissier, 50000);

    $this->actingAs($caissier)->post(route('caisse.sessions.mouvements.store', $session), [
        'type' => 'sortie',
        'montant' => 7500,
        'motif' => 'Achat rouleaux thermiques imprimante',
        'beneficiaire' => 'Papeterie Moderne',
    ])->assertRedirect();

    $session->refresh();

    expect((float) $session->total_sorties_especes)->toBe(7500.0)
        ->and($session->soldeTheoriqueAttendu())->toBe(42500.0) // 50 000 - 7 500
        ->and($session->mouvements()->count())->toBe(1);
});

test('la clôture de session avec billetage calcule fidèlement le réel compté et l écart de caisse', function () {
    $caissier = User::factory()->create(['role' => Role::Caissier]);
    $serviceCaisse = app(SessionCaisseService::class);
    $session = $serviceCaisse->ouvrir($caissier, 20000);

    $medicament = Medicament::factory()->create([
        'prix' => 5000,
        'stock' => 20,
        'date_expiration' => now()->addYear(),
    ]);

    // Vente de 3 boîtes en espèces = 15 000 FCFA. Solde théorique = 20 000 + 15 000 = 35 000 FCFA
    $actionVente = app(EnregistrerVenteAction::class);
    $actionVente->execute([$medicament->id => 3], 'especes', 15000, $caissier->id);

    // Billetage physique compté : 3 billets de 10 000 + 1 billet de 5 000 + 1 billet de 2 000 = 37 000 FCFA
    // Écart attendu : 37 000 - 35 000 = +2 000 FCFA (Excédent)
    $reponseCloture = $this->actingAs($caissier)->post(route('caisse.sessions.cloturer', $session), [
        'billetage' => [
            '10000' => 3,
            '5000' => 1,
            '2000' => 1,
        ],
        'observations' => 'Client a laissé un pourboire non enregistré',
    ]);

    $reponseCloture->assertRedirect(route('caisse.sessions.rapport-z', $session));

    $session->refresh();

    expect($session->statut)->toBe(StatutSessionCaisse::Cloturee)
        ->and($session->date_fermeture)->not->toBeNull()
        ->and((float) $session->montant_reel_compte)->toBe(37000.0)
        ->and((float) $session->ecart_caisse)->toBe(2000.0);
});

test('le rapport Z et le détail de session répondent avec succès', function () {
    $caissier = User::factory()->create(['role' => Role::Caissier]);
    $serviceCaisse = app(SessionCaisseService::class);
    $session = $serviceCaisse->ouvrir($caissier, 10000);

    $serviceCaisse->cloturer($session, ['10000' => 1]);

    $this->actingAs($caissier)->get(route('caisse.sessions.show', $session))
        ->assertOk()
        ->assertSee('Session de Caisse #' . $session->id);

    $this->actingAs($caissier)->get(route('caisse.sessions.rapport-z', $session))
        ->assertOk()
        ->assertSee('RAPPORT Z JOURNALIER');
});

test('l annulation d une vente déduit son montant des totaux théoriques de la session ouverte', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $serviceCaisse = app(SessionCaisseService::class);
    $session = $serviceCaisse->ouvrir($admin, 10000);

    $medicament = Medicament::factory()->create([
        'prix' => 4000,
        'stock' => 10,
        'date_expiration' => now()->addYear(),
    ]);

    $actionVente = app(EnregistrerVenteAction::class);
    $vente = $actionVente->execute([$medicament->id => 2], 'especes', 8000, $admin->id);

    $session->refresh();
    expect((float) $session->total_especes_theorique)->toBe(8000.0);

    // Annulation de la vente par l'administrateur
    $actionAnnuler = app(AnnulerVenteAction::class);
    $actionAnnuler->execute($vente);

    $session->refresh();
    expect((float) $session->total_especes_theorique)->toBe(0.0);
});
