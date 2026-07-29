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

Route::controller(DashboardController::class)->middleware('auth')->group(function () {
    Route::get('/dashboard', 'index')->name('dashboard');
    Route::get('/statistiques', 'statistiques')->name('dashboard.statistiques');
    Route::get('/stock', 'stockFaible')->name('dashboard.stockFaible');
    Route::get('/rupture', 'ruptureStock')->name('dashboard.ruptureStock');
    Route::get('/expire', 'expirationProche')->name('dashboard.expire');
});

Route::controller(VenteController::class)->middleware('auth')->group(function () {
    Route::get('/ventes', 'index')->name('ventes.index');
    Route::get('/ventes/create', 'create')->name('ventes.create');
    Route::post('/ventes/panier', 'panier')->name('ventes.panier');
    Route::get('/ventes/paiement', 'paiement')->name('ventes.paiement');
    Route::post('/ventes/store', 'store')->name('ventes.store');
    Route::get('/ventes/{vente}', 'show')->name('ventes.show');
    Route::delete('/ventes/{vente}', 'destroy')->name('ventes.destroy');
    Route::get('/ventes/{vente}/pdf', 'pdf')->name('ventes.pdf');
});

Route::controller(MedicamentController::class)->middleware('auth')->group(function () {
    Route::get('/liste', 'index')->name('medicaments.index');
    // recherche utilisée par l'écran de vente
    Route::get('/medicaments/search', 'search')->name('medicament.search');
    Route::get('/create', 'create')->name('medicament.create');
    Route::post('/create', 'store')->name('medicament.store');
    Route::put('/medicaments/{medicament}', 'update')->name('medicament.update');
    Route::delete('/medicaments/{medicament}', 'delete')->name('medicament.delete');
});
