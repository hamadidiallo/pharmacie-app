<?php

use App\Models\Medicament;
use App\Models\User;
use App\Models\Vente;

/** Dépose un panier en session via l'endpoint prévu, comme le fait l'écran de vente. */
function deposerPanier(array $lignes): void
{
    test()->postJson(route('ventes.panier'), ['panier' => $lignes])->assertOk();
}

test("l'écran de vente exige une connexion", function () {
    $this->get(route('ventes.create'))->assertRedirect(route('auth.login'));
    $this->get(route('ventes.paiement'))->assertRedirect(route('auth.login'));
    $this->postJson(route('ventes.panier'), ['panier' => []])->assertUnauthorized();
    $this->postJson(route('ventes.store'), [])->assertUnauthorized();

    expect(Vente::count())->toBe(0);
});

test('le panier est contrôlé avant le paiement, sans rien écrire', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['prix' => 1000, 'stock' => 2]);

    $this->actingAs($user)->postJson(route('ventes.panier'), [
        'panier' => [['id' => $medicament->id, 'quantite' => 5]],
    ])->assertStatus(422);

    expect(Vente::count())->toBe(0)
        ->and($medicament->fresh()->stock)->toBe(2)
        ->and(session()->has('panier'))->toBeFalse();
});

test("l'écran de paiement récapitule le panier au prix de la base", function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['nom' => 'Paracétamol 500mg', 'prix' => 1500, 'stock' => 10]);

    $this->actingAs($user);
    deposerPanier([['id' => $medicament->id, 'quantite' => 3]]);

    $this->get(route('ventes.paiement'))
        ->assertOk()
        ->assertSee('Paracétamol 500mg')
        ->assertSee('4 500'); // 3 × 1500
});

test('une vente en espèces décrémente le stock et calcule la monnaie', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['prix' => 1500, 'stock' => 10]);

    $this->actingAs($user);
    deposerPanier([['id' => $medicament->id, 'quantite' => 3]]);

    $this->post(route('ventes.store'), [
        'mode_paiement' => 'especes',
        'montant_recu' => 5000,
    ])->assertRedirect();

    $vente = Vente::sole();

    expect($vente->total)->toEqual(4500)
        ->and($vente->user_id)->toBe($user->id)
        ->and($vente->mode_paiement)->toBe('especes')
        ->and($vente->montant_recu)->toEqual(5000)
        ->and($vente->monnaie_rendue)->toEqual(500)
        ->and($medicament->fresh()->stock)->toBe(7)
        ->and($vente->medicaments()->first()->pivot->sous_total)->toEqual(4500)
        ->and(session()->has('panier'))->toBeFalse();
});

test('un paiement Mobile Money ne demande pas de monnaie', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['prix' => 2000, 'stock' => 5]);

    $this->actingAs($user);
    deposerPanier([['id' => $medicament->id, 'quantite' => 2]]);

    $this->post(route('ventes.store'), ['mode_paiement' => 'mobile_money'])->assertRedirect();

    $vente = Vente::sole();

    expect($vente->mode_paiement)->toBe('mobile_money')
        ->and($vente->montant_recu)->toBeNull()
        ->and($vente->monnaie_rendue)->toBeNull()
        ->and($vente->total)->toEqual(4000);
});

test('un montant reçu insuffisant refuse la vente', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['prix' => 2000, 'stock' => 5]);

    $this->actingAs($user);
    deposerPanier([['id' => $medicament->id, 'quantite' => 2]]);

    $this->post(route('ventes.store'), [
        'mode_paiement' => 'especes',
        'montant_recu' => 1000,
    ])->assertSessionHasErrors('montant_recu');

    expect(Vente::count())->toBe(0)
        ->and($medicament->fresh()->stock)->toBe(5);
});

test('un mode de paiement inconnu est refusé', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['prix' => 1000, 'stock' => 5]);

    $this->actingAs($user);
    deposerPanier([['id' => $medicament->id, 'quantite' => 1]]);

    $this->post(route('ventes.store'), ['mode_paiement' => 'bitcoin'])
        ->assertSessionHasErrors('mode_paiement');

    expect(Vente::count())->toBe(0);
});

test('le prix envoyé par le client est ignoré au profit de celui de la base', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['prix' => 2000, 'stock' => 5]);

    $this->actingAs($user);
    // tentative de fixer un prix arbitraire : le champ n'est même pas lu
    deposerPanier([['id' => $medicament->id, 'prix' => 1, 'quantite' => 2]]);

    $this->post(route('ventes.store'), ['mode_paiement' => 'carte']);

    expect(Vente::sole()->total)->toEqual(4000);
});

test('un stock devenu insuffisant entre le panier et le paiement annule tout', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['prix' => 1000, 'stock' => 10]);

    $this->actingAs($user);
    deposerPanier([['id' => $medicament->id, 'quantite' => 8]]);

    // quelqu'un d'autre vide le stock entre-temps
    $medicament->update(['stock' => 3]);

    $this->post(route('ventes.store'), ['mode_paiement' => 'carte'])->assertRedirect();

    expect(Vente::count())->toBe(0)
        ->and($medicament->fresh()->stock)->toBe(3);
});

test('un panier vide ou invalide est refusé', function () {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user)->postJson(route('ventes.panier'), ['panier' => []])->assertStatus(422);

    $this->actingAs($user)->postJson(route('ventes.panier'), [
        'panier' => [['id' => 1, 'quantite' => 0]],
    ])->assertStatus(422);

    // sans panier en session, l'écran de paiement renvoie au comptoir
    $this->actingAs($user)->get(route('ventes.paiement'))->assertRedirect(route('ventes.create'));

    expect(Vente::count())->toBe(0);
});

test('la suppression d’une vente restitue le stock', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['prix' => 1000, 'stock' => 10]);

    $this->actingAs($user);
    deposerPanier([['id' => $medicament->id, 'quantite' => 4]]);
    $this->post(route('ventes.store'), ['mode_paiement' => 'carte']);

    expect($medicament->fresh()->stock)->toBe(6);

    $vente = Vente::sole();

    $this->actingAs($user)
        ->delete(route('ventes.destroy', $vente))
        ->assertRedirect(route('ventes.index'));

    expect($medicament->fresh()->stock)->toBe(10)
        ->and(Vente::count())->toBe(0)
        ->and(DB::table('medicament__vente')->count())->toBe(0);
});
