<?php

namespace Tests\Feature;

use App\Enums\ResultStatus;
use App\Enums\StudentStatus;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class PublicPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function fakePassport(): UploadedFile
    {
        return UploadedFile::fake()->image('passport.jpg', 200, 200)->size(100);
    }

    public function test_public_registration_creates_pending_student(): void
    {
        $subjects = Subject::active()->take(3)->pluck('id')->toArray();

        $this->post(route('register.store'), [
            'surname' => 'Johnson',
            'first_name' => 'Grace',
            'middle_name' => 'Ade',
            'foundation_number' => 'PAAU/FS/PUB/01',
            'examination_number' => 'EXMPUB001',
            'subject_one_id' => $subjects[0],
            'subject_two_id' => $subjects[1],
            'subject_three_id' => $subjects[2],
            'passport' => $this->fakePassport(),
        ])->assertRedirect();

        $student = Student::where('foundation_number', 'PAAU/FS/PUB/01')->first();

        $this->assertNotNull($student);
        $this->assertSame(StudentStatus::Pending, $student->status);
        $this->assertSame($subjects[0], $student->subject_one_id);
        $this->assertSame($subjects[1], $student->subject_two_id);
        $this->assertSame($subjects[2], $student->subject_three_id);
    }

    public function test_duplicate_foundation_number_is_rejected(): void
    {
        $existing = Student::first();
        $subjects = Subject::active()->take(3)->pluck('id')->toArray();

        $this->post(route('register.store'), [
            'surname' => 'Duplicate',
            'first_name' => 'Student',
            'foundation_number' => $existing->foundation_number,
            'examination_number' => 'EXMDUP001',
            'subject_one_id' => $subjects[0],
            'subject_two_id' => $subjects[1],
            'subject_three_id' => $subjects[2],
            'passport' => $this->fakePassport(),
        ])->assertSessionHasErrors('foundation_number');
    }

    public function test_passport_upload_is_validated(): void
    {
        $subjects = Subject::active()->take(3)->pluck('id')->toArray();

        $this->post(route('register.store'), [
            'surname' => 'Bad',
            'first_name' => 'Upload',
            'foundation_number' => 'PAAU/FS/PUB/03',
            'examination_number' => 'EXMPUB003',
            'subject_one_id' => $subjects[0],
            'subject_two_id' => $subjects[1],
            'subject_three_id' => $subjects[2],
            'passport' => UploadedFile::fake()->create('doc.txt', 10),
        ])->assertSessionHasErrors('passport');
    }

    public function test_public_verify_shows_only_published_results(): void
    {
        $published = Result::where('status', ResultStatus::Published)->first();
        $draft = Result::where('status', ResultStatus::Draft)->first();

        Livewire::test('pages.verify')
            ->set('query', $published->student->foundation_number)
            ->set('token', $published->student->verification_token)
            ->call('verify')
            ->assertSet('verifiedStudentId', $published->student_id);

        Livewire::test('pages.verify')
            ->set('query', $draft->student->foundation_number)
            ->set('token', $draft->student->verification_token)
            ->call('verify')
            ->assertSet('verifiedStudentId', null);
    }

    public function test_verify_rejects_wrong_token(): void
    {
        $published = Result::where('status', ResultStatus::Published)->first();

        Livewire::test('pages.verify')
            ->set('query', $published->student->foundation_number)
            ->set('token', 'WRONG-TOKEN')
            ->call('verify')
            ->assertSet('verifiedStudentId', null);
    }

    public function test_verify_rejects_unknown_number(): void
    {
        Livewire::test('pages.verify')
            ->set('query', 'DOES-NOT-EXIST')
            ->set('token', 'ANY-TOKEN')
            ->call('verify')
            ->assertSet('verifiedStudentId', null);
    }
}
