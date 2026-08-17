<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::where('email', 'admin@paau.edu.ng')->first();

        $response = Livewire::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        Livewire::test('pages.auth.login')
            ->set('form.email', 'admin@paau.edu.ng')
            ->set('form.password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('form.email');

        $this->assertGuest();
    }

    public function test_navigation_menu_can_be_rendered(): void
    {
        $user = User::where('email', 'admin@paau.edu.ng')->first();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeVolt('layout.user-menu');
    }

    public function test_users_can_logout(): void
    {
        $user = User::where('email', 'admin@paau.edu.ng')->first();

        $this->actingAs($user);

        Livewire::test('layout.user-menu')
            ->call('logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
