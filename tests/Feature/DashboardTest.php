<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /** Memastikan tamu diarahkan ke halaman login saat membuka dashboard. */
    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    /** Memastikan pengguna terautentikasi diarahkan ke dashboard sesuai role. */
    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('peminjam.dashboard'));

        $this->get(route('peminjam.dashboard'))->assertOk();
    }
}
