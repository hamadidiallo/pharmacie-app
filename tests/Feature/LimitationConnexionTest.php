<?php

use App\Models\User;

test('les tentatives de connexion sont limitées', function () {
    User::factory()->create(['email' => 'pharmacien@example.com', 'password' => 'password']);

    // les cinq premières tentatives sont traitées normalement
    foreach (range(1, 5) as $tentative) {
        $this->post('/', [
            'email' => 'pharmacien@example.com',
            'password' => 'mauvais-mot-de-passe',
        ])->assertStatus(302);
    }

    // la sixième est bloquée
    $this->post('/', [
        'email' => 'pharmacien@example.com',
        'password' => 'mauvais-mot-de-passe',
    ])->assertStatus(429);

    $this->assertGuest();
});
