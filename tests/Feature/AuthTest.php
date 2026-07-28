<?php

use App\Models\User;

test('la page de connexion est accessible', function () {
    $this->get('/')->assertOk();
});

test('un utilisateur peut se connecter', function () {
    $user = User::factory()->create([
        'email' => 'pharmacien@example.com',
        'password' => 'password',
    ]);

    $this->post('/', [
        'email' => 'pharmacien@example.com',
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('des identifiants incorrects ne connectent personne', function () {
    User::factory()->create([
        'email' => 'pharmacien@example.com',
        'password' => 'password',
    ]);

    $this->post('/', [
        'email' => 'pharmacien@example.com',
        'password' => 'mauvais-mot-de-passe',
    ])->assertSessionHasErrors('error');

    $this->assertGuest();
});

test("l'inscription refuse un mot de passe trop court", function () {
    $this->post('/register', [
        'firstname' => 'Ali',
        'lastname' => 'Traoré',
        'email' => 'ali@example.com',
        'password' => 'court',
    ])->assertSessionHasErrors('password');

    expect(User::where('email', 'ali@example.com')->exists())->toBeFalse();
});
