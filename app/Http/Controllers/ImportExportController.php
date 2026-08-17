<?php

namespace App\Http\Controllers;

use App\Actions\Logs\LogActivity;
use App\Exports\ResultsExport;
use App\Exports\StudentsExport;
use App\Http\Requests\Student\ImportStudentsRequest;
use App\Imports\ResultsImport;
use App\Imports\StudentsImport;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

        $importer = new ResultsImport();

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
}
