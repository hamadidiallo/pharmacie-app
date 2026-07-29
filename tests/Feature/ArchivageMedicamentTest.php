<?php

use App\Models\Medicament;
use App\Models\User;
use App\Models\Vente;

/**
 * Régression : le pivot portait onDelete('cascade'), si bien que retirer un
 * médicament du catalogue effaçait ses lignes dans toutes les ventes passées.
 * Le ticket affichait alors un total sans aucun produit.
 */
test('retirer un médicament du catalogue préserve les ventes passées', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['nom' => 'Paracétamol 500mg', 'prix' => 1000, 'stock' => 10]);

    $this->actingAs($user);
    $this->postJson(route('ventes.panier'), [
        'panier' => [['id' => $medicament->id, 'quantite' => 3]],
    ])->assertOk();
    $this->post(route('ventes.store'), ['mode_paiement' => 'carte']);

    $vente = Vente::sole();

    expect($vente->total)->toEqual(3000)
        ->and($vente->medicaments)->toHaveCount(1);

    // le pharmacien retire le produit du catalogue
    $this->delete(route('medicaments.destroy', $medicament))->assertRedirect();

    $vente->refresh()->load('medicaments');

    // le ticket doit rester complet et cohérent
    expect($vente->total)->toEqual(3000)
        ->and(DB::table('medicament__vente')->count())->toBe(1)
        ->and($vente->medicaments)->toHaveCount(1)
        ->and($vente->medicaments->first()->nom)->toBe('Paracétamol 500mg')
        ->and($vente->medicaments->first()->pivot->sous_total)->toEqual(3000);
});

test('le ticket et son PDF restent lisibles après archivage', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['nom' => 'Amoxicilline 1g', 'prix' => 2000, 'stock' => 5]);

    $this->actingAs($user);
    $this->postJson(route('ventes.panier'), [
        'panier' => [['id' => $medicament->id, 'quantite' => 2]],
    ]);
    $this->post(route('ventes.store'), ['mode_paiement' => 'carte']);

    $vente = Vente::sole();
    $medicament->delete();

    $this->get(route('ventes.show', $vente))
        ->assertOk()
        ->assertSee('Amoxicilline 1g')
        ->assertSee('4 000');

    $this->get(route('ventes.pdf', $vente))->assertOk();
});

test('un médicament archivé disparaît du catalogue et ne peut plus être vendu', function () {
    $user = User::factory()->create();
    $medicament = Medicament::factory()->create(['nom' => 'Sirop retiré', 'stock' => 10]);

    $this->actingAs($user);
    $medicament->delete();

    // absent de la liste et de la recherche du comptoir
    $this->get(route('medicaments.index'))->assertOk()->assertDontSee('Sirop retiré');
    $this->getJson(route('medicaments.search', ['q' => 'Sirop']))->assertOk()->assertJsonCount(0);

    // et refusé à la mise au panier
    $this->postJson(route('ventes.panier'), [
        'panier' => [['id' => $medicament->id, 'quantite' => 1]],
    ])->assertStatus(404);

    expect(Vente::count())->toBe(0);
});

test("l'archivage ne compte plus le produit dans les statistiques de stock", function () {
    $user = User::factory()->create();
    Medicament::factory()->create(['stock' => 0]);
    $enRupture = Medicament::factory()->create(['stock' => 0]);

    expect(Medicament::enRupture()->count())->toBe(2);

    $enRupture->delete();

    expect(Medicament::enRupture()->count())->toBe(1);

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});
