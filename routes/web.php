<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicamentController;
use App\Http\Controllers\VenteController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::controller(AuthController::class)->group(function() {
    Route::get('/','showLoginForm')->name('auth.login')->middleware('guest');
    Route::post('/','login')->middleware('guest');
    Route::delete('/logout','logout')->name('auth.logout')->middleware('auth');
    Route::get('/register','showRegisterForm')->name('auth.register')->middleware('guest');
    Route::post('/register','register')->middleware('guest');
});
Route::controller(DashboardController::class)->group(function () {
    Route::get('/dashboard','index')->name('dashboard')->middleware('auth');
    Route::get('/top','top_produit')->name('dashboard.top_produit')->middleware('auth');
    Route::get('/stock','stockFaible')->name('dashboard.stockFaible')->middleware('auth');
    Route::get('/rupture','ruptureStock')->name('dashboard.ruptureStock')->middleware('auth');
    Route::get('/expire','expirationProche')->name('dashboard.expire')->middleware('auth');

});
Route::controller(VenteController::class)->group(function () {
    Route::get('/ventes','index')->name('ventes.index')->middleware('auth');
    Route::get('/ventes/create','create')->name('ventes.create');
    Route::post('/ventes/store','store')->name('ventes.store');
    Route::get('/ventes/{vente}','show')->name('ventes.show')->middleware('auth');
    Route::delete('/ventes/{vente}', 'destroy')->name('ventes.destroy')->middleware('auth');
    Route::get('/ventes/{vente}/pdf','pdf')->name('ventes.pdf')->middleware('auth');
    Route::get('/{medicament}/search','search')->name('medicament.search')->middleware('auth');

});
Route::controller(MedicamentController::class)->group(function () {
    Route::get('/liste','index')->name('medicaments.index')->middleware('auth');
    Route::get('/stocks','stocks')->middleware('auth');
    Route::get('/create','create')->name('medicament.create')->middleware('auth');
    Route::post('/create','store')->name('medicament.store')->middleware('auth');
    Route::put('/{medicament}/update','update')->name('medicament.update')->middleware('auth');
    Route::delete('/{medicament}/delete','delete')->name('medicament.delete')->middleware('auth');

}); 

