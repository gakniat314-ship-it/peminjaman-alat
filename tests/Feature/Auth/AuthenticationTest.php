<?php

use App\Models\User;

// Memastikan halaman login ditampilkan menggunakan view autentikasi.
test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertOk()->assertViewIs('auth.login');
});

// Memastikan kredensial valid membuat pengguna terautentikasi dan diarahkan.
test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

// Memastikan kata sandi yang salah tidak membuat pengguna terautentikasi.
test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

// Memastikan pengguna dapat mengakhiri sesi dan kembali ke halaman utama.
test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
