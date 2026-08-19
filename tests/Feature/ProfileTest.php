<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = User::where('email', 'admin@paau.edu.ng')->first();

        $response = $this->actingAs($user)->get(route('admin.profile'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::where('email', 'admin@paau.edu.ng')->first();

        $this->actingAs($user);

        $component = Volt::test('pages.admin.profile')
            ->set('name', 'Test Admin')
            ->set('email', 'updated@paau.edu.ng')
            ->call('updateProfile');

        $component->assertHasNoErrors();

        $user->refresh();

        $this->assertSame('Test Admin', $user->name);
        $this->assertSame('updated@paau.edu.ng', $user->email);
    }

    public function test_password_can_be_updated(): void
    {
        $user = User::where('email', 'admin@paau.edu.ng')->first();

        $this->actingAs($user);

        $component = Volt::test('pages.admin.profile')
            ->set('current_password', 'password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('updatePassword');

        $component->assertHasNoErrors();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::where('email', 'admin@paau.edu.ng')->first();

        $this->actingAs($user);

        $component = Volt::test('pages.admin.profile')
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('updatePassword');

        $component->assertHasErrors('current_password');
    }
}
