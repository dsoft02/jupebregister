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

        Excel::import($importer, $request->file('file'));

        $log->run(
            action: 'students.imported',
            description: 'Imported a student spreadsheet — '
                .$importer->created.' created, '.$importer->updated.' updated, '.$importer->skipped.' skipped',
            properties: ['created' => $importer->created, 'updated' => $importer->updated, 'skipped' => $importer->skipped],
        );

        return back()->with('status', "Import complete: {$importer->created} created, {$importer->updated} updated, {$importer->skipped} skipped.");
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
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:5120'],
        ]);

        $importer = new ResultsImport;

        Excel::import($importer, $request->file('file'));

        $log->run(
            action: 'results.imported',
            description: 'Imported results spreadsheet — '
                .$importer->created.' created, '.$importer->updated.' updated, '.$importer->skipped.' skipped',
            properties: ['created' => $importer->created, 'updated' => $importer->updated, 'skipped' => $importer->skipped],
        );

        return back()->with('status', "Import complete: {$importer->created} created, {$importer->updated} updated, {$importer->skipped} skipped.");
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
            'results' => response()->streamDownload(function () {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['examination_number', 'grade_one', 'grade_two', 'grade_three']);
                fputcsv($handle, ['EXM001', 'A', 'B', 'C']);
                fputcsv($handle, ['EXM002', 'B', 'C', 'D']);
                fclose($handle);
            }, 'results-sample-template.csv', $headers),

            'subjects' => response()->streamDownload(function () {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['name', 'is_active']);
                fputcsv($handle, ['Mathematics', '1']);
                fputcsv($handle, ['English Language', '1']);
                fputcsv($handle, ['Physics', '1']);
                fclose($handle);
            }, 'subjects-sample-template.csv', $headers),

            default => abort(404),
        };
    }

    public function importSubjects(Request $request, LogActivity $log): RedirectResponse
    {
        $this->authorize('import', \App\Models\Student::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:5120'],
        ]);

        $importer = new SubjectsImport;

        Excel::import($importer, $request->file('file'));

        $log->run(
            action: 'subjects.imported',
            description: 'Imported subjects spreadsheet — '
                .$importer->created.' created, '.$importer->updated.' updated, '.$importer->skipped.' skipped',
            properties: ['created' => $importer->created, 'updated' => $importer->updated, 'skipped' => $importer->skipped],
        );

        return back()->with('status', "Import complete: {$importer->created} created, {$importer->updated} updated, {$importer->skipped} skipped.");
    }
}
