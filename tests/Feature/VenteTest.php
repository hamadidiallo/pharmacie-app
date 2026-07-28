<?php

use App\Models\Medicament;
use App\Models\User;
use App\Models\Vente;

test("l'écran de vente exige une connexion", function () {
    $this->get(route('ventes.create'))->assertRedirect(route('auth.login'));

    $this->postJson(route('ventes.store'), ['panier' => []])->assertUnauthorized();

    expect(Vente::count())->toBe(0);
});

test('une vente décrémente le stock et enregistre les lignes', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['prix' => 1500, 'stock' => 10]);

    $reponse = $this->actingAs($user)->postJson(route('ventes.store'), [
        'panier' => [
            ['id' => $medicament->id, 'prix' => 1500, 'quantite' => 3],
        ],
    ])->assertOk();

    $vente = Vente::find($reponse->json('vente_id'));

    expect($vente->total)->toEqual(4500)
        ->and($vente->user_id)->toBe($user->id)
        ->and($medicament->fresh()->stock)->toBe(7)
        ->and($vente->medicaments()->first()->pivot->sous_total)->toEqual(4500);
});

test('le prix envoyé par le client est ignoré au profit de celui de la base', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['prix' => 2000, 'stock' => 5]);

    $reponse = $this->actingAs($user)->postJson(route('ventes.store'), [
        'panier' => [
            // tentative de fixer un prix arbitraire
            ['id' => $medicament->id, 'prix' => 1, 'quantite' => 2],
        ],
    ])->assertOk();

    expect(Vente::find($reponse->json('vente_id'))->total)->toEqual(4000);
});

test('un stock insuffisant annule toute la vente', function () {
    $user = User::factory()->create();
    $disponible = Medicament::factory()->create(['prix' => 1000, 'stock' => 10]);
    $insuffisant = Medicament::factory()->create(['prix' => 1000, 'stock' => 1]);

    $this->actingAs($user)->postJson(route('ventes.store'), [
        'panier' => [
            ['id' => $disponible->id, 'prix' => 1000, 'quantite' => 2],
            ['id' => $insuffisant->id, 'prix' => 1000, 'quantite' => 5],
        ],
    ])->assertStatus(422);

    // rien ne doit avoir été écrit : ni vente, ni décrément de stock
    expect(Vente::count())->toBe(0)
        ->and($disponible->fresh()->stock)->toBe(10)
        ->and($insuffisant->fresh()->stock)->toBe(1);
});

test('un panier vide ou invalide est refusé', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('ventes.store'), ['panier' => []])
        ->assertStatus(422);

    $this->actingAs($user)->postJson(route('ventes.store'), [
        'panier' => [['id' => 1, 'quantite' => 0]],
    ])->assertStatus(422);

    expect(Vente::count())->toBe(0);
});

test('la suppression d’une vente restitue le stock', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['prix' => 1000, 'stock' => 10]);

    $reponse = $this->actingAs($user)->postJson(route('ventes.store'), [
        'panier' => [
            ['id' => $medicament->id, 'prix' => 1000, 'quantite' => 4],
        ],
    ]);

    expect($medicament->fresh()->stock)->toBe(6);

    $vente = Vente::find($reponse->json('vente_id'));

    $this->actingAs($user)
        ->delete(route('ventes.destroy', $vente))
        ->assertRedirect(route('ventes.index'));

    expect($medicament->fresh()->stock)->toBe(10)
        ->and(Vente::count())->toBe(0)
        ->and(DB::table('medicament__vente')->count())->toBe(0);
});
