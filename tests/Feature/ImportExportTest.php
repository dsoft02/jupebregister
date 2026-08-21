<?php

namespace Tests\Feature;

use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = \App\Models\User::where('email', 'admin@paau.edu.ng')->first();
    }

    public function test_import_page_renders_and_uses_distinct_file_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.import-export.create'));

        $response->assertOk();
        $response->assertSee('name="students_file"', false);
        $response->assertSee('name="results_file"', false);
        $response->assertSee('name="subjects_file"', false);
    }

    public function test_students_can_be_imported_from_csv(): void
    {
        $csv = implode("\n", [
            'surname,first_name,middle_name,foundation_number,examination_number,subject_one,subject_two,subject_three',
            'Okafor,Chinedu,,PAAU/FS/2026/001,PAAU-EXM-202601,Biology,Chemistry,Physics',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.import.store'), [
                'students_file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
            ]);

        $response->assertRedirect()
            ->assertSessionHas('status');

        $student = Student::where('foundation_number', 'PAAU/FS/2026/001')->first();

        $this->assertNotNull($student);
        $this->assertSame('Okafor', $student->surname);
        $this->assertSame('PAAU-EXM-202601', $student->examination_number);
    }

    public function test_results_can_be_imported_from_csv(): void
    {
        $subjects = Subject::active()->take(3)->pluck('id')->toArray();
        $student = Student::create([
            'surname' => 'Import',
            'first_name' => 'Target',
            'foundation_number' => 'PAAU/FS/TST/99',
            'examination_number' => 'PAAU-EXM-9999',
            'subject_one_id' => $subjects[0],
            'subject_two_id' => $subjects[1],
            'subject_three_id' => $subjects[2],
        ]);

        $csv = implode("\n", [
            'examination_number,grade_one,grade_two,grade_three',
            "{$student->examination_number},A,B,C",
            'UNKNOWN-NUMBER,A,B,C',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.results.import'), [
                'results_file' => UploadedFile::fake()->createWithContent('results.csv', $csv),
            ]);

        $response->assertRedirect()
            ->assertSessionHas('status');

        $this->assertNotNull(
            Result::where('student_id', $student->id)->where('grade_one', 'A')->first()
        );
    }

    public function test_subjects_can_be_imported_from_plain_csv_without_comma_content_sniffing_issues(): void
    {
        // Single-column CSV: finfo reports it as text/plain, which used to fail
        // the mimes rule. The extensions rule accepts it based on the extension.
        $csv = "name\nFurther Mathematics\n";

        $response = $this->actingAs($this->admin)
            ->post(route('admin.subjects.import'), [
                'subjects_file' => UploadedFile::fake()->createWithContent('subjects.csv', $csv),
            ]);

        $response->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('subjects', ['name' => 'Further Mathematics']);
    }

    public function test_validation_error_is_scoped_to_the_submitted_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.subjects.import'), []);

        $response->assertRedirect()
            ->assertSessionHasErrors(['subjects_file']);

        $errors = session('errors')->getBag('default')->keys();

        $this->assertEquals(['subjects_file'], $errors);
        $this->assertNotContains('students_file', $errors);
        $this->assertNotContains('results_file', $errors);
    }

    public function test_unreadable_spreadsheet_redirects_back_with_friendly_error(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.subjects.import'), [
                'subjects_file' => UploadedFile::fake()->createWithContent('broken.csv', "\x00\x01not-a-spreadsheet"),
            ]);

        $response->assertRedirect()
            ->assertSessionHasErrors('subjects_file');

        $this->assertStringContainsString(
            'could not be processed',
            session('errors')->getBag('default')->first('subjects_file')
        );
    }

    public function test_sample_templates_contain_a_single_record(): void
    {
        foreach (['students', 'results', 'subjects'] as $type) {
            $response = $this->actingAs($this->admin)
                ->get(route('admin.sample-template', $type));

            $response->assertOk();

            $rows = array_filter(explode("\n", trim($response->streamedContent())));

            $this->assertCount(2, $rows, "Sample template [$type] must have a header plus exactly one record.");
            $this->assertStringEndsWith('csv', $response->headers->get('content-disposition'));
        }
    }
}
