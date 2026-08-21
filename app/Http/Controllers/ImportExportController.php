<?php

namespace App\Http\Controllers;

use App\Actions\Logs\LogActivity;
use App\Exports\ResultsExport;
use App\Exports\StudentsExport;
use App\Http\Requests\Student\ImportStudentsRequest;
use App\Imports\ResultsImport;
use App\Imports\StudentsImport;
use App\Imports\SubjectsImport;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportExportController extends Controller
{
    private const SPREADSHEET_RULE = ['required', 'file', 'extensions:csv,xlsx,xls', 'max:5120'];

    public function create(): \Illuminate\View\View
    {
        return view('admin.import-export', [
            'subjects' => Subject::active()->orderBy('name')->get(),
        ]);
    }

    public function import(ImportStudentsRequest $request, LogActivity $log): RedirectResponse
    {
        $this->authorize('import', \App\Models\Student::class);

        $importer = new StudentsImport($request->boolean('update_existing'));

        if ($error = $this->runImport(fn () => Excel::import($importer, $request->file('students_file')))) {
            return back()->withErrors(['students_file' => $error]);
        }

        $log->run(
            action: 'students.imported',
            description: 'Imported a student spreadsheet — '
                .$importer->created.' created, '.$importer->updated.' updated, '.$importer->skipped.' skipped',
            properties: ['created' => $importer->created, 'updated' => $importer->updated, 'skipped' => $importer->skipped],
        );

        return back()->with(
            'status',
            "Import complete: {$importer->created} created, {$importer->updated} updated, {$importer->skipped} skipped."
        );
    }

    public function export(Request $request, string $format): BinaryFileResponse
    {
        $this->authorize('export', \App\Models\Student::class);

        $format = $format === 'xlsx' ? 'xlsx' : 'csv';
        $filters = $request->only(['session', 'status', 'combination']);

        $extension = $format === 'xlsx' ? 'xlsx' : 'csv';

        return Excel::download(
            new StudentsExport($filters),
            'students-'.now()->format('Ymd-His').'.'.$extension,
        );
    }

    public function importResults(Request $request, LogActivity $log): RedirectResponse
    {
        $this->authorize('enter', \App\Models\Result::class);

        $request->validate([
            'results_file' => self::SPREADSHEET_RULE,
        ], [
            'results_file.required' => 'Please choose a spreadsheet to import.',
            'results_file.extensions' => 'The file must be a CSV or Excel (xlsx/xls) document.',
        ], ['results_file' => 'file']);

        $importer = new ResultsImport;

        if ($error = $this->runImport(fn () => Excel::import($importer, $request->file('results_file')))) {
            return back()->withErrors(['results_file' => $error]);
        }

        $log->run(
            action: 'results.imported',
            description: 'Imported results spreadsheet — '
                .$importer->created.' created, '.$importer->updated.' updated, '.$importer->skipped.' skipped',
            properties: ['created' => $importer->created, 'updated' => $importer->updated, 'skipped' => $importer->skipped],
        );

        return back()->with(
            'status',
            "Import complete: {$importer->created} created, {$importer->updated} updated, {$importer->skipped} skipped."
        );
    }

    public function exportResults(Request $request, string $format): BinaryFileResponse
    {
        $this->authorize('export', \App\Models\Result::class);

        $format = $format === 'xlsx' ? 'xlsx' : 'csv';
        $filters = $request->only(['session', 'status']);

        $extension = $format === 'xlsx' ? 'xlsx' : 'csv';

        return Excel::download(
            new ResultsExport($filters),
            'results-'.now()->format('Ymd-His').'.'.$extension,
        );
    }

    public function sampleTemplate(string $type): StreamedResponse
    {
        $this->authorize('import', \App\Models\Student::class);

        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment'];

        return match ($type) {
            'students' => response()->streamDownload(function () {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, [
                    'surname', 'first_name', 'middle_name', 'foundation_number',
                    'examination_number', 'subject_one', 'subject_two', 'subject_three',
                ]);
                fputcsv($handle, [
                    'Doe', 'John', '', 'PAAU/FS/2025/001', 'PAAU-EXM-0001',
                    'Biology', 'Chemistry', 'Physics',
                ]);
                fclose($handle);
            }, 'students-sample-template.csv', $headers),

            'results' => response()->streamDownload(function () {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['examination_number', 'grade_one', 'grade_two', 'grade_three']);
                fputcsv($handle, ['PAAU-EXM-0001', 'A', 'B', 'C']);
                fclose($handle);
            }, 'results-sample-template.csv', $headers),

            'subjects' => response()->streamDownload(function () {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['name', 'is_active']);
                fputcsv($handle, ['Mathematics', '1']);
                fclose($handle);
            }, 'subjects-sample-template.csv', $headers),

            default => abort(404),
        };
    }

    public function importSubjects(Request $request, LogActivity $log): RedirectResponse
    {
        $this->authorize('import', \App\Models\Student::class);

        $request->validate([
            'subjects_file' => self::SPREADSHEET_RULE,
        ], [
            'subjects_file.required' => 'Please choose a spreadsheet to import.',
            'subjects_file.extensions' => 'The file must be a CSV or Excel (xlsx/xls) document.',
        ], ['subjects_file' => 'file']);

        $importer = new SubjectsImport;

        if ($error = $this->runImport(fn () => Excel::import($importer, $request->file('subjects_file')))) {
            return back()->withErrors(['subjects_file' => $error]);
        }

        $log->run(
            action: 'subjects.imported',
            description: 'Imported subjects spreadsheet — '
                .$importer->created.' created, '.$importer->updated.' updated, '.$importer->skipped.' skipped',
            properties: ['created' => $importer->created, 'updated' => $importer->updated, 'skipped' => $importer->skipped],
        );

        return back()->with(
            'status',
            "Import complete: {$importer->created} created, {$importer->updated} updated, {$importer->skipped} skipped."
        );
    }

    /**
     * Run an import and convert any failure into a user-friendly message.
     *
     * @param  callable(): void  $callback
     * @return string|null Error message, or null when the import succeeded.
     */
    private function runImport(callable $callback): ?string
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            report($e);

            return 'The spreadsheet could not be processed. Please use the sample template as a guide and try again.';
        }

        return null;
    }
}
