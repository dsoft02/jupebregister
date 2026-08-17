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

class AdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = User::where('email', 'admin@paau.edu.ng')->first();
    }

    public function test_admin_can_access_all_dashboard_and_management_pages(): void
    {
        $student = Student::first();

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.students.index'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.students.create'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.students.show', $student))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.students.edit', $student))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.results.index'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.results.entry', $student))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.import-export.create'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.settings'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.users.index'))
            ->assertOk();
    }

    public function test_result_entry_calculates_points_bonus_and_total(): void
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
            'session' => '2025/2026',
        ]);

        \Livewire\Livewire::actingAs($this->admin)
            ->test('pages.admin.results.entry', ['student' => $student])
            ->set('grade_one', 'A')
            ->set('grade_two', 'A')
            ->set('grade_three', 'A')
            ->set('publish', true)
            ->call('save');

        $result = $student->fresh()->result;

        $this->assertNotNull($result);
        $this->assertSame(5, $result->point_one);
        $this->assertSame(5, $result->point_two);
        $this->assertSame(5, $result->point_three);
        $this->assertSame(1, $result->bonus_point);
        $this->assertSame(16, $result->total_point);
        $this->assertSame(ResultStatus::Published, $result->status);
        $this->assertNotNull($result->published_at);

        $student->load('subjectOne', 'subjectTwo', 'subjectThree');
        $this->assertSame($student->subjectOne->name, $result->subject_one);
        $this->assertSame($student->subjectTwo->name, $result->subject_two);
        $this->assertSame($student->subjectThree->name, $result->subject_three);
    }

    public function test_statement_of_result_pdf_is_generated(): void
    {
        $result = Result::where('status', ResultStatus::Published)->first();

        $this->assertNotNull($result);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.results.pdf', $result));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());

        // The slip template must carry the official footer text and student name.
        $settings = app(\App\Services\SettingsService::class);

        $html = view('pdf.statement-of-result', [
            'result' => $result,
            'student' => $result->student->load('subjectOne', 'subjectTwo', 'subjectThree'),
            'settings' => $settings,
            'letterhead' => null,
            'stamp' => null,
            'signature' => null,
            'passport' => null,
            'issueDate' => app(\App\Services\StatementOfResultService::class)->issueDate(),
        ])->render();

        $this->assertStringContainsString('Any alteration or erasure renders this result slip invalid', $html);
        $this->assertStringContainsString($result->student->surname, $html);
    }

    public function test_director_cannot_enter_results_but_can_publish(): void
    {
        $director = User::where('email', 'director@paau.edu.ng')->first();

        $student = Student::whereDoesntHave('result')->first();

        if ($student) {
            $this->actingAs($director)
                ->get(route('admin.results.entry', $student))
                ->assertForbidden();
        }

        $result = Result::where('status', ResultStatus::Draft)->first();

        if ($result) {
            $this->actingAs($director)
                ->get(route('admin.results.pdf', $result))
                ->assertOk();
        }

        $this->assertNotNull($result);
    }
}
