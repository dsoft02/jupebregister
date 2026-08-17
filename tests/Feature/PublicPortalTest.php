<?php

namespace Tests\Feature;

use App\Enums\ResultStatus;
use App\Enums\StudentStatus;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_public_registration_creates_pending_student(): void
    {
        $subjects = Subject::active()->take(3)->pluck('id')->toArray();

        $this->post(route('register.store'), [
            'surname' => 'Johnson',
            'first_name' => 'Grace',
            'middle_name' => 'Ade',
            'foundation_number' => 'PAAU/FS/PUB/001',
            'jupeb_number' => '23J/9001',
            'examination_number' => 'PAAU-EXM-PUB-001',
            'subject_one_id' => $subjects[0],
            'subject_two_id' => $subjects[1],
            'subject_three_id' => $subjects[2],
            'phone' => '+2348000000000',
            'email' => 'grace@example.com',
        ])->assertRedirect();

        $student = Student::where('foundation_number', 'PAAU/FS/PUB/001')->first();

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
            'subject_one_id' => $subjects[0],
            'subject_two_id' => $subjects[1],
            'subject_three_id' => $subjects[2],
            'phone' => '+2348000000000',
        ])->assertSessionHasErrors('foundation_number');
    }

    public function test_duplicate_jupeb_number_is_rejected(): void
    {
        $existing = Student::whereNotNull('jupeb_number')->first();
        $subjects = Subject::active()->take(3)->pluck('id')->toArray();

        $this->post(route('register.store'), [
            'surname' => 'Duplicate',
            'first_name' => 'Student',
            'foundation_number' => 'PAAU/FS/PUB/002',
            'jupeb_number' => $existing->jupeb_number,
            'subject_one_id' => $subjects[0],
            'subject_two_id' => $subjects[1],
            'subject_three_id' => $subjects[2],
            'phone' => '+2348000000000',
        ])->assertSessionHasErrors('jupeb_number');
    }

    public function test_passport_upload_is_validated(): void
    {
        $subjects = Subject::active()->take(3)->pluck('id')->toArray();

        $this->post(route('register.store'), [
            'surname' => 'Bad',
            'first_name' => 'Upload',
            'foundation_number' => 'PAAU/FS/PUB/003',
            'subject_one_id' => $subjects[0],
            'subject_two_id' => $subjects[1],
            'subject_three_id' => $subjects[2],
            'phone' => '+2348000000000',
            'passport' => \Illuminate\Http\UploadedFile::fake()->create('doc.txt', 10),
        ])->assertSessionHasErrors('passport');
    }

    public function test_public_verify_shows_only_published_results(): void
    {
        $published = Result::where('status', ResultStatus::Published)->first();
        $draft = Result::where('status', ResultStatus::Draft)->first();

        // Published result must verify.
        Livewire::test('pages.verify')
            ->set('query', $published->student->foundation_number)
            ->call('verify')
            ->assertSet('verifiedStudentId', $published->student_id);

        // Draft (unpublished) result must not verify.
        Livewire::test('pages.verify')
            ->set('query', $draft->student->foundation_number)
            ->call('verify')
            ->assertSet('verifiedStudentId', null);
    }

    public function test_verify_rejects_unknown_number(): void
    {
        Livewire::test('pages.verify')
            ->set('query', 'DOES-NOT-EXIST')
            ->call('verify')
            ->assertSet('verifiedStudentId', null);
    }
}
