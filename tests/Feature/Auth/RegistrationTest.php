<?php

namespace Tests\Feature\Auth;

use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /**
     * The /register route is reserved for the public student registration
     * portal. Staff accounts are created by admins from the Users page.
     */
    public function test_public_student_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSee('Student Registration')
            ->assertSee('Foundation Number');
    }

    public function test_registration_form_shows_subjects(): void
    {
        $this->get('/register')
            ->assertSee(Subject::active()->first()->name);
    }
}
