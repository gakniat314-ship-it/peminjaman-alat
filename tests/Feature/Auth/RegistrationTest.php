<?php

// Memastikan halaman pendaftaran dapat ditampilkan kepada pengunjung.
test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

// Memastikan formulir pendaftaran membuat akun dan mengautentikasi pengguna.
test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

// Memastikan redirect Inertia beralih ke dashboard di jendela utama, bukan modal error.
test('inertia registration navigates to the dashboard with a full page visit', function () {
    $response = $this->post('/register', [
        'name' => 'Inertia Test User',
        'email' => 'inertia-test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ], [
        'X-Inertia' => 'true',
    ]);

    $this->assertAuthenticated();
    $response->assertStatus(409)
        ->assertHeader('X-Inertia-Location', route('dashboard'));
});
