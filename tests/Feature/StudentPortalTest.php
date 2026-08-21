<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class StudentPortalTest extends TestCase
{
    use RefreshDatabase;

    private function registerStudent(array $overrides = []): Student
    {
        $subjects = \App\Models\Subject::active()->take(3)->pluck('id')->toArray();

        $this->post(route('register.store'), [
            'surname' => 'Johnson',
            'first_name' => 'Grace',
            'middle_name' => 'Ade',
            'foundation_number' => $overrides['foundation_number'] ?? 'PAAU/FS/PUB/01',
            'examination_number' => $overrides['examination_number'] ?? 'EXMPUB001',
            'subject_one_id' => $subjects[0],
            'subject_two_id' => $subjects[1],
            'subject_three_id' => $subjects[2],
            'passport' => UploadedFile::fake()->image('passport.jpg', 200, 200)->size(100),
            ...$overrides,
        ])->assertRedirect();

        return Student::where('foundation_number', $overrides['foundation_number'] ?? 'PAAU/FS/PUB/01')->first();
    }

    public function test_registration_provisions_account_with_default_password(): void
    {
        $this->seed();

        $student = $this->registerStudent();

        $this->assertNotNull($student->user);
        $this->assertTrue($student->user->hasRole(UserRole::Student->value));
        $this->assertTrue(password_verify('password', $student->user->password));
    }

    public function test_student_can_login_with_foundation_number_and_default_password(): void
    {
        $this->seed();
        $student = $this->registerStudent();

        Livewire::test('pages.auth.login')
            ->set('form.email', strtolower($student->foundation_number))
            ->set('form.password', 'password')
            ->call('login');

        $this->assertAuthenticated();
        $user = auth()->user();
        $this->assertTrue($user->isStudent());
        $this->assertSame($student->id, $user->student_id);
    }

    public function test_wrong_password_is_rejected_for_students(): void
    {
        $this->seed();
        $student = $this->registerStudent();

        Livewire::test('pages.auth.login')
            ->set('form.email', $student->foundation_number)
            ->set('form.password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('form.email');

        $this->assertGuest();
    }

    public function test_student_can_update_profile_including_foundation_number(): void
    {
        $this->seed();
        $student = $this->registerStudent();
        $other = $this->registerStudent(['foundation_number' => 'PAAU/FS/PUB/02', 'examination_number' => 'EXMPUB002']);

        $user = $student->user;

        $newFn = 'PAAU/FS/MINE/1';
        $this->assertSame(14, strlen($newFn));

        $this->actingAs($user);

        Livewire::test('pages.student.profile')
            ->set('surname', 'Changed')
            ->set('foundation_number', $newFn)
            ->call('save')
            ->assertDispatched('flash-message');

        $student->refresh();
        $this->assertSame('Changed', $student->surname);
        $this->assertSame($newFn, $student->foundation_number);
        $this->assertSame(Str::slug($newFn).'@students.paau.edu.ng', $user->refresh()->email);

        // cannot take another student's foundation number
        if (strlen($other->foundation_number) === 14) {
            Livewire::test('pages.student.profile')
                ->set('foundation_number', $other->foundation_number)
                ->call('save')
                ->assertHasErrors('foundation_number');
        }
    }

    public function test_guests_cannot_access_student_portal(): void
    {
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
        $this->get(route('student.profile'))->assertRedirect(route('login'));
        $this->get(route('student.statement'))->assertRedirect(route('login'));
    }

    public function test_staff_cannot_access_student_portal(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@paau.edu.ng')->first();

        $this->actingAs($admin)->get(route('student.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('student.statement'))->assertForbidden();
    }

    public function test_login_page_still_works_with_email_for_staff(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@paau.edu.ng')->first();

        Livewire::test('pages.auth.login')
            ->set('form.email', $admin->email)
            ->set('form.password', 'password')
            ->call('login');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_statement_page_shows_published_result_and_download(): void
    {
        $this->seed();
        $student = $this->registerStudent();
        $student->update(['status' => \App\Enums\StudentStatus::Approved]);

        // Shows a friendly notice before publication.
        $this->actingAs($student->user)
            ->get(route('student.statement'))
            ->assertOk()
            ->assertSee('has not been published yet')
            ->assertDontSee('Download Statement of Result');

        $subjects = $student->chosenSubjectNames();

        $result = app(\App\Actions\Results\UpsertResult::class)->run($student->id, [
            'subject_one' => $subjects[0],
            'subject_two' => $subjects[1],
            'subject_three' => $subjects[2],
            'grade_one' => 'A',
            'grade_two' => 'B',
            'grade_three' => 'A',
            'status' => \App\Enums\ResultStatus::Draft->value,
        ]);

        // Draft results show the notice too.
        $this->actingAs($student->user)
            ->get(route('student.statement'))
            ->assertOk()
            ->assertSee('has not been published yet')
            ->assertDontSee('Download Statement of Result');

        $result->update(['status' => \App\Enums\ResultStatus::Published->value, 'published_at' => now()]);

        $this->actingAs($student->user)
            ->get(route('student.statement'))
            ->assertOk()
            ->assertSee('Download Statement of Result')
            ->assertSee($student->verification_token);

        $this->actingAs($student->user)
            ->get(route('results.download', $result))
            ->assertOk();
    }

    public function test_cancel_edit_restores_original_values(): void
    {
        $this->seed();
        $student = $this->registerStudent();

        Livewire::actingAs($student->user)
            ->test('pages.student.profile')
            ->call('startEdit')
            ->assertSet('editing', true)
            ->set('surname', 'Mutated')
            ->call('cancelEdit')
            ->assertSet('editing', false)
            ->assertSet('surname', 'Johnson');
    }

    public function test_passport_is_required_when_student_has_none(): void
    {
        $this->seed();
        $student = $this->registerStudent();

        $student->update(['passport' => null]);

        Livewire::actingAs($student->user)
            ->test('pages.student.profile')
            ->call('startEdit')
            ->call('save')
            ->assertHasErrors('passport');
    }

    public function test_subject_selects_prevent_duplicate_selection(): void
    {
        $this->seed();
        $student = $this->registerStudent();

        $response = Livewire::actingAs($student->user)
            ->test('pages.student.profile');

        $html = $response->html();

        // three searchable selects wired to Livewire
        $this->assertSame(1, substr_count($html, 'wire:model.live="subject_one_id"'));
        $this->assertSame(1, substr_count($html, 'wire:model.live="subject_two_id"'));
        $this->assertSame(1, substr_count($html, 'wire:model.live="subject_three_id"'));
        $this->assertSame(3, substr_count($html, 'Type to search'));

        // current selections are pre-selected
        foreach ($student->fresh()->chosenSubjects() as $subject) {
            $this->assertStringContainsString("selectedLabel: '".$subject->name."'", $html);
        }
    }

    public function test_student_can_change_password(): void
    {
        $this->seed();
        $student = $this->registerStudent();
        $user = $student->user;

        $this->assertTrue(password_verify('password', $user->password));

        Livewire::actingAs($user)
            ->test('pages.student.profile')
            ->set('current_password', 'password')
            ->set('new_password', 'new-secret-123')
            ->set('new_password_confirmation', 'new-secret-123')
            ->call('updatePassword')
            ->assertDispatched('flash-message');

        $this->assertTrue(password_verify('new-secret-123', $user->refresh()->password));
        $this->assertFalse(password_verify('password', $user->password));
    }

    public function test_password_change_rejects_wrong_current_password(): void
    {
        $this->seed();
        $student = $this->registerStudent();
        $user = $student->user;

        Livewire::actingAs($user)
            ->test('pages.student.profile')
            ->set('current_password', 'not-my-password')
            ->set('new_password', 'new-secret-123')
            ->set('new_password_confirmation', 'new-secret-123')
            ->call('updatePassword')
            ->assertHasErrors('current_password');

        $this->assertTrue(password_verify('password', $user->refresh()->password));
    }

    public function test_portal_uses_sidebar_layout_with_menu_items(): void
    {
        $this->seed();
        $student = $this->registerStudent();

        $response = $this->actingAs($student->user)->get(route('student.profile'));

        $response->assertOk()
            ->assertSee('My Profile', false)
            ->assertSee('Statement of Result', false);
    }
}
