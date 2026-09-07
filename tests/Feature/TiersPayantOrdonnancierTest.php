<?php

use App\Enums\Role;
use App\Enums\StatutBordereauAssurance;
use App\Enums\StatutLot;
use App\Enums\TableauReglementaire;
use App\Models\Assurance;
use App\Models\BordereauAssurance;
use App\Models\Medicament;
use App\Models\MedicamentLot;
use App\Models\OrdonnancierLigne;
use App\Models\SessionCaisse;
use App\Models\User;
use App\Models\Vente;

beforeEach(function () {
    $this->pharmacien = User::factory()->create(['role' => Role::Pharmacien]);
    $this->actingAs($this->pharmacien);
});

test('une vente avec tiers payant ventile la part patient et la part assurance', function () {
    $assurance = Assurance::create([
        'nom' => 'CANAM AMO',
        'code' => 'CANAM',
        'taux_couverture_defaut' => 70.00,
        'delai_remboursement_jours' => 30,
        'est_actif' => true,
    ]);

    $med = Medicament::factory()->create([
        'nom' => 'Doliprane 1000mg',
        'prix' => 10000,
        'stock' => 50,
        'tableau' => TableauReglementaire::NonListe,
        'ordonnance_requise' => false,
    ]);

    MedicamentLot::create([
        'medicament_id' => $med->id,
        'numero_lot' => 'LOT-CANAM-01',
        'date_expiration' => now()->addYear(),
        'quantite_initiale' => 50,
        'quantite_actuelle' => 50,
        'statut' => StatutLot::Actif,
    ]);

    $session = SessionCaisse::create([
        'user_id' => $this->pharmacien->id,
        'date_ouverture' => now(),
        'fond_caisse_ouverture' => 10000,
        'total_especes_theorique' => 10000,
    ]);

    $sessionData = ['panier' => [$med->id => 1]];

    $response = $this->withSession($sessionData)->post(route('ventes.store'), [
        'mode_paiement' => 'especes',
        'montant_recu' => 3000, // Patient ne paye que son ticket modérateur (30%)
        'avec_assurance' => 1,
        'assurance_id' => $assurance->id,
        'matricule_assure' => 'AMO-998877',
        'nom_assure' => 'Amadou Diallo',
        'taux_couverture' => 70,
    ]);

    $response->assertRedirect();

    $vente = Vente::latest('id')->first();
    expect($vente)->not->toBeNull()
        ->and($vente->total)->toEqual(10000)
        ->and($vente->part_assurance)->toEqual(7000)
        ->and($vente->part_patient)->toEqual(3000)
        ->and($vente->statut_remboursement)->toBe('en_attente')
        ->and($vente->assurance_id)->toBe($assurance->id)
        ->and($vente->matricule_assure)->toBe('AMO-998877');

    // La caisse ne doit avoir encaissé que la part patient (3000 FCFA)
    $session->refresh();
    expect($session->total_especes_theorique)->toEqual(13000);
});

test('un bordereau de facturation regroupe les ventes assurées d une période', function () {
    $assurance = Assurance::create([
        'nom' => 'INPS Santé',
        'code' => 'INPS',
        'taux_couverture_defaut' => 80.00,
        'delai_remboursement_jours' => 45,
        'est_actif' => true,
    ]);

    $med = Medicament::factory()->create([
        'prix' => 5000,
        'stock' => 50,
        'tableau' => TableauReglementaire::NonListe,
        'ordonnance_requise' => false,
    ]);

    MedicamentLot::create([
        'medicament_id' => $med->id,
        'numero_lot' => 'LOT-INPS-01',
        'date_expiration' => now()->addYear(),
        'quantite_initiale' => 50,
        'quantite_actuelle' => 50,
        'statut' => StatutLot::Actif,
    ]);

    // 2 ventes avec tiers payant INPS
    $this->withSession(['panier' => [$med->id => 1]])->post(route('ventes.store'), [
        'mode_paiement' => 'carte',
        'avec_assurance' => 1,
        'assurance_id' => $assurance->id,
        'matricule_assure' => 'INPS-001',
        'taux_couverture' => 80,
    ]);

    $this->withSession(['panier' => [$med->id => 2]])->post(route('ventes.store'), [
        'mode_paiement' => 'carte',
        'avec_assurance' => 1,
        'assurance_id' => $assurance->id,
        'matricule_assure' => 'INPS-002',
        'taux_couverture' => 80,
    ]);

    // Génération du bordereau
    $response = $this->post(route('bordereaux.store'), [
        'assurance_id' => $assurance->id,
        'periode_debut' => now()->startOfMonth()->toDateString(),
        'periode_fin' => now()->endOfMonth()->toDateString(),
        'notes' => 'Facturation INPS Mensuelle',
    ]);

    $response->assertRedirect();

    $bordereau = BordereauAssurance::latest('id')->first();
    expect($bordereau)->not->toBeNull()
        ->and($bordereau->assurance_id)->toBe($assurance->id)
        ->and($bordereau->nombre_dossiers)->toBe(2)
        // Vente 1 : 5000 * 80% = 4000 ; Vente 2 : 10000 * 80% = 8000 ; Total = 12000
        ->and($bordereau->montant_total)->toEqual(12000)
        ->and($bordereau->statut)->toBe(StatutBordereauAssurance::Brouillon);
});

test('la transmission et le règlement d un bordereau soldent les factures de l assurance', function () {
    $assurance = Assurance::create([
        'nom' => 'NSIA Assurances',
        'code' => 'NSIA',
        'taux_couverture_defaut' => 80.00,
        'est_actif' => true,
    ]);

    $med = Medicament::factory()->create([
        'prix' => 10000,
        'stock' => 10,
        'tableau' => TableauReglementaire::NonListe,
        'ordonnance_requise' => false,
    ]);

    MedicamentLot::create([
        'medicament_id' => $med->id,
        'numero_lot' => 'LOT-NSIA-01',
        'date_expiration' => now()->addYear(),
        'quantite_initiale' => 10,
        'quantite_actuelle' => 10,
        'statut' => StatutLot::Actif,
    ]);

    $this->withSession(['panier' => [$med->id => 1]])->post(route('ventes.store'), [
        'mode_paiement' => 'carte',
        'avec_assurance' => 1,
        'assurance_id' => $assurance->id,
        'matricule_assure' => 'NSIA-777',
        'taux_couverture' => 80,
    ]);

    $this->post(route('bordereaux.store'), [
        'assurance_id' => $assurance->id,
        'periode_debut' => now()->subDay()->toDateString(),
        'periode_fin' => now()->addDay()->toDateString(),
    ]);

    $bordereau = BordereauAssurance::latest('id')->first();

    // 1. Transmission
    $this->post(route('bordereaux.transmettre', $bordereau));
    $bordereau->refresh();
    expect($bordereau->statut)->toBe(StatutBordereauAssurance::Transmis);

    // 2. Règlement
    $this->post(route('bordereaux.regler', $bordereau), [
        'mode_reglement' => 'Virement bancaire',
        'reference_reglement' => 'VIR-BDM-2026-99',
        'notes' => 'Payé avec succès',
    ]);

    $bordereau->refresh();
    expect($bordereau->statut)->toBe(StatutBordereauAssurance::Regle)
        ->and($bordereau->reference_reglement)->toBe('VIR-BDM-2026-99');

    // Les ventes liées doivent être marquées "rembourse"
    $vente = $bordereau->ventes()->first();
    expect($vente->statut_remboursement)->toBe('rembourse');
});

test('la vente d un stupéfiant exige prescripteur et patient et s inscrit à l ordonnancier officiel', function () {
    $stupefiant = Medicament::factory()->create([
        'nom' => 'Morphine Sulfate 10mg',
        'prix' => 6000,
        'stock' => 20,
        'tableau' => TableauReglementaire::Stupefiant,
        'ordonnance_requise' => true,
    ]);

    MedicamentLot::create([
        'medicament_id' => $stupefiant->id,
        'numero_lot' => 'LOT-STUP-2026',
        'date_expiration' => now()->addYear(),
        'quantite_initiale' => 20,
        'quantite_actuelle' => 20,
        'statut' => StatutLot::Actif,
    ]);

    // Échec sans nom de médecin prescripteur ni patient
    $responseRefus = $this->withSession(['panier' => [$stupefiant->id => 1]])
        ->from(route('ventes.paiement'))
        ->post(route('ventes.store'), [
            'mode_paiement' => 'carte',
        ]);

    $responseRefus->assertRedirect(route('ventes.paiement'))
        ->assertSessionHas('alert');

    // Succès avec mentions réglementaires fournies
    $responseSucces = $this->withSession(['panier' => [$stupefiant->id => 2]])
        ->post(route('ventes.store'), [
            'mode_paiement' => 'carte',
            'nom_prescripteur' => 'Dr Ousmane Diarra',
            'specialite_prescripteur' => 'Anesthésiste-Réanimateur',
            'nom_patient' => 'Fatoumata Coulibaly',
            'age_patient' => 45,
            'posologie' => '1 ampoule toutes les 6h si douleur aiguë',
        ]);

    $responseSucces->assertRedirect();

    $ligneOrdonnancier = OrdonnancierLigne::where('nom_patient', 'Fatoumata Coulibaly')->first();
    expect($ligneOrdonnancier)->not->toBeNull()
        ->and($ligneOrdonnancier->nom_prescripteur)->toBe('Dr Ousmane Diarra')
        ->and($ligneOrdonnancier->medicament_id)->toBe($stupefiant->id)
        ->and($ligneOrdonnancier->quantite_delivree)->toBe(2)
        ->and($ligneOrdonnancier->numero_ordonnancier)->toStartWith('ORD-' . date('Y') . '-');
});

test('les registres d ordonnancier et bordereaux sont consultables et imprimables', function () {
    $assurance = Assurance::create([
        'nom' => 'Test Assur',
        'code' => 'TEST',
        'taux_couverture_defaut' => 70,
        'est_actif' => true,
    ]);

    $bordereau = BordereauAssurance::create([
        'reference' => 'BORD-TEST-001',
        'assurance_id' => $assurance->id,
        'periode_debut' => now(),
        'periode_fin' => now(),
        'montant_total' => 15000,
        'nombre_dossiers' => 1,
    ]);

    // Pages Assurances & Bordereaux
    $this->get(route('assurances.index'))->assertOk();
    $this->get(route('bordereaux.index'))->assertOk();
    $this->get(route('bordereaux.create'))->assertOk();
    $this->get(route('bordereaux.show', $bordereau))->assertOk();
    $this->get(route('bordereaux.imprimer', $bordereau))->assertOk();

    // Pages Ordonnancier
    $this->get(route('ordonnancier.index'))->assertOk();
    $this->get(route('ordonnancier.imprimer'))->assertOk();
});
