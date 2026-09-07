<?php

use App\Http\Controllers\AssuranceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BordereauAssuranceController;
use App\Http\Controllers\CommandeFournisseurController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\MedicamentController;
use App\Http\Controllers\OrdonnancierController;
use App\Http\Controllers\SessionCaisseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VenteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module 1 : Authentification & Sessions
|--------------------------------------------------------------------------
*/
Route::controller(AuthController::class)->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/', 'showLoginForm')->name('auth.login');
        Route::get('/register', 'showRegisterForm')->name('auth.register');

        // Limite les tentatives pour prévenir les attaques par force brute
        Route::middleware('throttle:5,1')->group(function () {
            Route::post('/', 'login');
            Route::post('/register', 'register');
        });
    });

    Route::delete('/logout', 'logout')->name('auth.logout')->middleware('auth');
});

/*
|--------------------------------------------------------------------------
| Espace Officine Authentifié
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |----------------------------------------------------------------------
    | Module 2 : Tableau de bord & Analytics
    |----------------------------------------------------------------------
    */
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/statistiques', 'statistiques')->name('dashboard.statistiques')->middleware('can:acceder-statistiques');
    });

    /*
    |----------------------------------------------------------------------
    | Module 3 : Catalogue & Gestion du Stock
    |----------------------------------------------------------------------
    */
    // Redirections pour compatibilité avec anciens liens / favoris
    Route::redirect('/stock', '/medicaments?filtre=faible');
    Route::redirect('/rupture', '/medicaments?filtre=rupture');
    Route::redirect('/expire', '/medicaments?filtre=expire');

    Route::controller(MedicamentController::class)
        ->prefix('medicaments')
        ->name('medicaments.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/search', 'search')->name('search');

            // Actions réservées au Pharmacien et à l'Administrateur
            Route::middleware('can:gerer-medicaments')->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::put('/{medicament}', 'update')->name('update');
                Route::delete('/{medicament}', 'destroy')->name('destroy');

                // Traçabilité des Lots (FEFO, Arrivages & Sécurité sanitaire)
                Route::post('/{medicament}/lots', 'ajouterLot')->name('lots.store');
                Route::post('/lots/{lot}/isoler', 'isolerLot')->name('lots.isoler');
                Route::post('/lots/{lot}/reactiver', 'reactiverLot')->name('lots.reactiver');
            });
        });

    /*
    |----------------------------------------------------------------------
    | Module 4 : Caisse & Ventes au comptoir
    |----------------------------------------------------------------------
    */
    Route::controller(VenteController::class)
        ->prefix('ventes')
        ->name('ventes.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::get('/paiement', 'paiement')->name('paiement');
            Route::post('/panier', 'panier')->name('panier');
            Route::post('/', 'store')->name('store');
            Route::get('/{vente}', 'show')->name('show');
            Route::get('/{vente}/pdf', 'pdf')->name('pdf');

            // Seul l'Administrateur peut annuler une vente passée
            Route::delete('/{vente}', 'destroy')->name('destroy')->middleware('can:supprimer-vente');
        });

    /*
    |----------------------------------------------------------------------
    | Module 4-B : Gestion de Caisse Avancée, Billetage & Rapport Z
    |----------------------------------------------------------------------
    */
    Route::controller(SessionCaisseController::class)
        ->prefix('caisse/sessions')
        ->name('caisse.sessions.')
        ->middleware('can:gerer-caisse')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{session}', 'show')->name('show');
            Route::get('/{session}/cloture', 'clotureForm')->name('cloture');
            Route::post('/{session}/cloture', 'cloturer')->name('cloturer');
            Route::get('/{session}/rapport-z', 'rapportZ')->name('rapport-z');
            Route::post('/{session}/mouvements', 'storeMouvement')->name('mouvements.store');
        });

    /*
    |----------------------------------------------------------------------
    | Module 5 : Approvisionnements & Fournisseurs (Grossistes)
    |----------------------------------------------------------------------
    */
    Route::middleware('can:gerer-medicaments')->group(function () {
        // Répertoire des fournisseurs
        Route::controller(FournisseurController::class)
            ->prefix('fournisseurs')
            ->name('fournisseurs.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('/{fournisseur}', 'update')->name('update');
                Route::delete('/{fournisseur}', 'destroy')->name('destroy');
            });

        // Commandes d'approvisionnement & Réceptions BL
        Route::controller(CommandeFournisseurController::class)
            ->prefix('commandes')
            ->name('commandes.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/suggerer', 'suggerer')->name('suggerer');
                Route::post('/generer-automatique', 'genererAutomatique')->name('generer-automatique');
                Route::get('/{commande}', 'show')->name('show');
                Route::get('/{commande}/bon-commande', 'bonCommande')->name('bon-commande');
                Route::post('/{commande}/envoyer', 'envoyer')->name('envoyer');
                Route::get('/{commande}/reception', 'receptionnerForm')->name('reception');
                Route::post('/{commande}/reception', 'enregistrerReception')->name('enregistrer-reception');
            });
    });

    /*
    |----------------------------------------------------------------------
    | Module 7 : Tiers-Payant, Assurances & Bordereaux de Remboursement
    |----------------------------------------------------------------------
    */
    Route::middleware('can:gerer-medicaments')->group(function () {
        // Organismes payeurs & mutuelles
        Route::controller(AssuranceController::class)
            ->prefix('assurances')
            ->name('assurances.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('/{assurance}', 'update')->name('update');
                Route::delete('/{assurance}', 'destroy')->name('destroy');
            });

        // Bordereaux de facturation groupée
        Route::controller(BordereauAssuranceController::class)
            ->prefix('bordereaux')
            ->name('bordereaux.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{bordereau}', 'show')->name('show');
                Route::post('/{bordereau}/transmettre', 'transmettre')->name('transmettre');
                Route::post('/{bordereau}/regler', 'regler')->name('regler');
                Route::get('/{bordereau}/imprimer', 'imprimer')->name('imprimer');
            });
    });

    /*
    |----------------------------------------------------------------------
    | Module 8 : Ordonnancier Numérique Réglementaire & Registre Stupéfiants
    |----------------------------------------------------------------------
    */
    Route::controller(OrdonnancierController::class)
        ->prefix('ordonnancier')
        ->name('ordonnancier.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/imprimer', 'imprimer')->name('imprimer');
        });

    /*
    |----------------------------------------------------------------------
    | Module 9 : Administration des Utilisateurs & Rôles
    |----------------------------------------------------------------------
    */
    Route::controller(UserController::class)
        ->prefix('utilisateurs')
        ->name('users.')
        ->middleware('can:admin')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{user}', 'update')->name('update');
            Route::delete('/{user}', 'destroy')->name('destroy');
        });
});
