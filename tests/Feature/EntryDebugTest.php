<?php

namespace Tests\Feature;

use App\Enums\ResultStatus;
use App\Enums\StudentStatus;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntryDebugTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@paau.edu.ng')->first();
    }

    public function test_debug_entry(): void
    {
        $subjects = Subject::active()->take(3)->pluck('id')->toArray();
        $student = Student::create([
            'surname' => 'Test',
            'first_name' => 'Student',
            'foundation_number' => 'PAAU/FS/TEST/001',
            'examination_number' => 'PAAU/EXM/TEST/001',
            'subject_one_id' => $subjects[0],
            'subject_two_id' => $subjects[1],
            'subject_three_id' => $subjects[2],
            'status' => StudentStatus::Approved,
        ]);

        \Livewire\Livewire::actingAs($this->admin)
            ->test('pages.admin.results.entry', ['student' => $student])
            ->set('grade_one', 'A')
            ->set('grade_two', 'A')
            ->set('grade_three', 'A')
            ->set('publish', true)
            ->call('save');

        $result = $student->fresh()->currentResult();

        fwrite(STDERR, "result: ".($result ? 'FOUND '.$result->total_point : 'NULL')."\n");
        fwrite(STDERR, "fresh result: ".($student->fresh()->currentResult() ? 'FOUND '.$student->fresh()->currentResult()->total_point : 'NULL')."\n");
        fwrite(STDERR, "all results count: ".Result::count()."\n");
        fwrite(STDERR, "latest result student: ".Result::latest('id')->first()?->student_id."\n");
        $this->assertTrue(true);
    }
}
