<?php

use App\Enums\Role;
use App\Models\Medicament;
use App\Models\User;
use App\Models\Vente;

test('un caissier ne peut pas accéder aux statistiques financières', function () {
    $caissier = User::factory()->caissier()->create();

    $this->actingAs($caissier)
        ->get(route('dashboard.statistiques'))
        ->assertForbidden();
});

test('un pharmacien et un administrateur peuvent accéder aux statistiques', function () {
    $pharmacien = User::factory()->pharmacien()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($pharmacien)
        ->get(route('dashboard.statistiques'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('dashboard.statistiques'))
        ->assertOk();
});

test('un caissier ne peut pas ajouter, modifier ou supprimer un médicament', function () {
    $caissier = User::factory()->caissier()->create();
    $medicament = Medicament::factory()->create();

    $this->actingAs($caissier)
        ->get(route('medicaments.create'))
        ->assertForbidden();

    $this->actingAs($caissier)
        ->post(route('medicaments.store'), [
            'nom' => 'Interdit',
            'prix' => 1000,
            'stock' => 10,
            'date_expiration' => now()->addYear()->toDateString(),
            'description' => 'Test',
        ])
        ->assertForbidden();

    $this->actingAs($caissier)
        ->put(route('medicaments.update', $medicament), [
            'nom' => 'Modifié',
            'prix' => 1200,
            'stock' => 5,
            'date_expiration' => now()->addYear()->toDateString(),
            'description' => 'Test',
        ])
        ->assertForbidden();

    $this->actingAs($caissier)
        ->delete(route('medicaments.destroy', $medicament))
        ->assertForbidden();
});

test('un pharmacien peut gérer le catalogue de médicaments', function () {
    $pharmacien = User::factory()->pharmacien()->create();
    $medicament = Medicament::factory()->create();

    $this->actingAs($pharmacien)
        ->get(route('medicaments.create'))
        ->assertOk();

    $this->actingAs($pharmacien)
        ->post(route('medicaments.store'), [
            'nom' => 'Nouveau Produit',
            'prix' => 1500,
            'stock' => 20,
            'date_expiration' => now()->addYear()->toDateString(),
            'description' => 'Description produit',
        ])
        ->assertRedirect(route('medicaments.index'));

    $this->actingAs($pharmacien)
        ->delete(route('medicaments.destroy', $medicament))
        ->assertRedirect(route('medicaments.index'));
});

test('seul un administrateur peut supprimer une vente passée', function () {
    $caissier = User::factory()->caissier()->create();
    $pharmacien = User::factory()->pharmacien()->create();
    $admin = User::factory()->admin()->create();

    $medicament = Medicament::factory()->create(['stock' => 10, 'prix' => 1000]);
    $vente = Vente::create([
        'total' => 1000,
        'date_vente' => now(),
        'user_id' => $admin->id,
        'mode_paiement' => 'especes',
    ]);
    $vente->medicaments()->attach($medicament->id, [
        'quantite' => 1,
        'prix' => 1000,
        'sous_total' => 1000,
    ]);

    // Le caissier ne peut pas supprimer la vente
    $this->actingAs($caissier)
        ->delete(route('ventes.destroy', $vente))
        ->assertForbidden();

    // Le pharmacien ne peut pas non plus supprimer la vente
    $this->actingAs($pharmacien)
        ->delete(route('ventes.destroy', $vente))
        ->assertForbidden();

    // L'administrateur peut supprimer la vente
    $this->actingAs($admin)
        ->delete(route('ventes.destroy', $vente))
        ->assertRedirect(route('ventes.index'));

    expect(Vente::find($vente->id))->toBeNull();
});

test('tous les rôles authentifiés peuvent encaisser une vente', function () {
    $caissier = User::factory()->caissier()->create();
    $medicament = Medicament::factory()->create(['stock' => 50, 'prix' => 2000]);

    $this->actingAs($caissier);

    $this->postJson(route('ventes.panier'), [
        'panier' => [['id' => $medicament->id, 'quantite' => 2]],
    ])->assertOk();

    $this->post(route('ventes.store'), [
        'mode_paiement' => 'carte',
    ])->assertRedirect();

    expect(Vente::count())->toBe(1);
});

test('seul un administrateur peut accéder à la gestion des utilisateurs', function () {
    $caissier = User::factory()->caissier()->create();
    $pharmacien = User::factory()->pharmacien()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($caissier)
        ->get(route('users.index'))
        ->assertForbidden();

    $this->actingAs($pharmacien)
        ->get(route('users.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee('Gestion des utilisateurs');
});

test('un administrateur peut créer un employé avec son rôle, username et téléphone', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'firstname' => 'Moussa',
            'lastname' => 'Diarra',
            'username' => 'moussa_d',
            'email' => 'moussa@gestapharm.ml',
            'telephone' => '+223 70 12 34 56',
            'role' => Role::Caissier->value,
            'password' => 'password123',
        ])
        ->assertRedirect(route('users.index'));

    $user = User::where('email', 'moussa@gestapharm.ml')->first();
    expect($user)->not->toBeNull()
        ->and($user->username)->toBe('moussa_d')
        ->and($user->telephone)->toBe('+223 70 12 34 56')
        ->and($user->role)->toBe(Role::Caissier)
        ->and($user->isCaissier())->toBeTrue();
});

test('un administrateur peut modifier le rôle d’un utilisateur', function () {
    $admin = User::factory()->admin()->create();
    $employe = User::factory()->caissier()->create();

    $this->actingAs($admin)
        ->put(route('users.update', $employe), [
            'firstname' => $employe->firstname,
            'lastname' => $employe->lastname,
            'email' => $employe->email,
            'role' => Role::Pharmacien->value,
        ])
        ->assertRedirect(route('users.index'));

    expect($employe->fresh()->role)->toBe(Role::Pharmacien);
});

test('un administrateur ne peut pas supprimer son propre compte', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $admin))
        ->assertRedirect(route('users.index'));

    expect(User::find($admin->id))->not->toBeNull();
});

test('un administrateur peut supprimer un autre compte', function () {
    $admin = User::factory()->admin()->create();
    $autre = User::factory()->caissier()->create();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $autre))
        ->assertRedirect(route('users.index'));

    expect(User::find($autre->id))->toBeNull();
});
