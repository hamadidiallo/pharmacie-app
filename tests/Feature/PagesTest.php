<?php

use App\Models\Medicament;
use App\Models\User;
use App\Models\Vente;

test('les pages publiques répondent', function () {
    $this->get(route('auth.login'))->assertOk()->assertSee('Connexion');
    $this->get(route('auth.register'))->assertOk()->assertSee('Créer un compte');
});

test('les pages authentifiées répondent', function (string $route) {
    $user = User::factory()->create();
    Medicament::factory()->count(3)->create();

    $this->actingAs($user)->get(route($route))->assertOk();
})->with([
    'dashboard',
    'dashboard.statistiques',
    'medicaments.index',
    'medicaments.create',
    'ventes.index',
    'ventes.create',
]);

test('les anciennes URL des pages d\'alerte redirigent vers la liste filtrée', function (string $ancienne, string $filtre) {
    $user = User::factory()->create();

    $this->actingAs($user)->get($ancienne)
        ->assertRedirect('/medicaments?filtre=' . $filtre);
})->with([
    ['/rupture', 'rupture'],
    ['/stock', 'faible'],
    ['/expire', 'expire'],
]);

test('les statistiques acceptent les trois périodes', function (string $periode) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard.statistiques', ['periode' => $periode]))
        ->assertOk();
})->with(['semaine', 'mois', 'trimestre']);

test('les filtres de la liste des médicaments fonctionnent', function () {
    $user = User::factory()->create();

    Medicament::factory()->create(['nom' => 'Bien fourni', 'stock' => 100]);
    Medicament::factory()->create(['nom' => 'Presque fini', 'stock' => 2]);
    Medicament::factory()->create(['nom' => 'Épuisé', 'stock' => 0]);

    $this->actingAs($user)->get(route('medicaments.index', ['filtre' => 'rupture']))
        ->assertOk()
        ->assertSee('Épuisé')
        ->assertDontSee('Bien fourni');

    $this->actingAs($user)->get(route('medicaments.index', ['filtre' => 'faible']))
        ->assertOk()
        ->assertSee('Presque fini')
        ->assertDontSee('Épuisé');

    $this->actingAs($user)->get(route('medicaments.index', ['q' => 'fourni']))
        ->assertOk()
        ->assertSee('Bien fourni')
        ->assertDontSee('Presque fini');
});

test('le ticket de vente et son PDF sont générés', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['prix' => 1000, 'stock' => 10]);

    $this->actingAs($user);
    $this->postJson(route('ventes.panier'), [
        'panier' => [['id' => $medicament->id, 'quantite' => 2]],
    ])->assertOk();
    $this->post(route('ventes.store'), ['mode_paiement' => 'especes', 'montant_recu' => 5000]);

    $vente = Vente::sole();

    $this->actingAs($user)->get(route('ventes.show', $vente))
        ->assertOk()
        ->assertSee('TCK-' . $vente->id)
        ->assertSee('Espèces');

    $this->actingAs($user)->get(route('ventes.pdf', $vente))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('la liste des médicaments affiche le formulaire de modification et le menu', function () {
    $user = User::factory()->create();
    $medicaments = Medicament::factory()->count(3)->create();

    $html = $this->actingAs($user)->get(route('medicaments.index'))
        ->assertOk()
        // le partiel d'édition est bien inclus dans chaque modale
        ->assertSee(route('medicaments.update', $medicaments->first()), false)
        ->getContent();

    // le layout et la barre latérale ne doivent être rendus qu'une seule fois :
    // le partiel d'édition ne doit surtout pas ré-étendre le layout
    expect(substr_count($html, '<!doctype html>'))->toBe(1)
        ->and(substr_count($html, 'id="sidebar"'))->toBe(1);
});
