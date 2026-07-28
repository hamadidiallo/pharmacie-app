<?php

use App\Models\Medicament;
use App\Models\User;
use App\Models\Vente;

test('les pages publiques répondent', function () {
    $this->get(route('auth.login'))->assertOk()->assertSee('CONNEXION', false);
    $this->get(route('auth.register'))->assertOk();
});

test('les pages authentifiées répondent', function (string $route) {
    $user = User::factory()->create();
    Medicament::factory()->count(3)->create();

    $this->actingAs($user)->get(route($route))->assertOk();
})->with([
    'dashboard',
    'dashboard.top_produit',
    'dashboard.stockFaible',
    'dashboard.ruptureStock',
    'dashboard.expire',
    'medicaments.index',
    'medicament.create',
    'ventes.index',
    'ventes.create',
]);

test('le ticket de vente et son PDF sont générés', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['prix' => 1000, 'stock' => 10]);

    $reponse = $this->actingAs($user)->postJson(route('ventes.store'), [
        'panier' => [
            ['id' => $medicament->id, 'prix' => 1000, 'quantite' => 2],
        ],
    ]);

    $vente = Vente::find($reponse->json('vente_id'));

    $this->actingAs($user)->get(route('ventes.show', $vente))->assertOk();
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
        ->assertSee(route('medicament.update', $medicaments->first()), false)
        ->getContent();

    // le layout et le menu ne doivent être rendus qu'une seule fois :
    // le partiel d'édition ne doit surtout pas ré-étendre le layout
    expect(substr_count($html, '<!doctype html>'))->toBe(1)
        ->and(substr_count($html, 'id="menu-principal"'))->toBe(1);
});
