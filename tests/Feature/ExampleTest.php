<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

// Memastikan halaman publik menampilkan konten untuk pengunjung tanpa akun.
it('shows the public landing page to guests', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('auth.user', null),
        );
});

// Memastikan pengguna yang login tetap dapat mengakses halaman publik.
it('keeps the public landing page available to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('auth.user.id', $user->id),
        );
});
