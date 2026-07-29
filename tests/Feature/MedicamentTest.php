<?php

use App\Models\Medicament;
use App\Models\User;

test('les écrans médicaments exigent une connexion', function () {
    $this->get(route('medicaments.index'))->assertRedirect(route('auth.login'));
    $this->get(route('medicaments.create'))->assertRedirect(route('auth.login'));
});

test('un médicament est créé et rattaché à son auteur', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('medicaments.store'), [
        'nom' => 'Paracétamol 500mg',
        'prix' => 1500,
        'stock' => 20,
        'date_expiration' => now()->addYear()->format('Y-m-d'),
        'description' => 'Antalgique',
    ])->assertRedirect(route('medicaments.index'));

    $medicament = Medicament::firstWhere('nom', 'Paracétamol 500mg');

    expect($medicament)->not->toBeNull()
        ->and($medicament->stock)->toBe(20)
        ->and($medicament->user_id)->toBe($user->id);
});

test('un lot identique cumule le stock au lieu de créer un doublon', function () {
    $user = User::factory()->create();

    $donnees = [
        'nom' => 'Ibuprofène 400mg',
        'prix' => 2000,
        'stock' => 10,
        'date_expiration' => now()->addYear()->format('Y-m-d'),
        'description' => 'Anti-inflammatoire',
    ];

    $this->actingAs($user)->post(route('medicaments.store'), $donnees);
    $this->actingAs($user)->post(route('medicaments.store'), $donnees);

    expect(Medicament::where('nom', 'Ibuprofène 400mg')->count())->toBe(1)
        ->and(Medicament::firstWhere('nom', 'Ibuprofène 400mg')->stock)->toBe(20);
});

test('un médicament peut être supprimé', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create();

    $this->actingAs($user)
        ->delete(route('medicaments.destroy', $medicament))
        ->assertRedirect(route('medicaments.index'));

    expect(Medicament::find($medicament->id))->toBeNull();
});
