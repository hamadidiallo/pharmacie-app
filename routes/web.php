<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicamentController;
use App\Http\Controllers\VenteController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/', 'showLoginForm')->name('auth.login');
        Route::get('/register', 'showRegisterForm')->name('auth.register');

        // limite les tentatives : sans cela la force brute est libre
        Route::middleware('throttle:5,1')->group(function () {
            Route::post('/', 'login');
            Route::post('/register', 'register');
        });
    });
    Route::delete('/logout', 'logout')->name('auth.logout')->middleware('auth');
});

Route::middleware('auth')->group(function () {

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/statistiques', 'statistiques')->name('dashboard.statistiques');
    });

    /*
     * Les anciennes pages d'alerte faisaient doublon avec la liste filtrée, qui
     * offre en plus la recherche, la pagination et les actions. Les URL sont
     * conservées pour ne casser aucun signet ni lien existant.
     */
    Route::redirect('/stock', '/medicaments?filtre=faible');
    Route::redirect('/rupture', '/medicaments?filtre=rupture');
    Route::redirect('/expire', '/medicaments?filtre=expire');

    Route::controller(MedicamentController::class)
        ->prefix('medicaments')
        ->name('medicaments.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            // avant /{medicament} pour que « search » ne soit pas pris pour un identifiant
            Route::get('/search', 'search')->name('search');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::put('/{medicament}', 'update')->name('update');
            Route::delete('/{medicament}', 'destroy')->name('destroy');
        });

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
            Route::delete('/{vente}', 'destroy')->name('destroy');
            Route::get('/{vente}/pdf', 'pdf')->name('pdf');
        });
});
