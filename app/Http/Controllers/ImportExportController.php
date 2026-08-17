<?php

namespace App\Http\Controllers;

use App\Actions\Logs\LogActivity;
use App\Exports\StudentsExport;
use App\Http\Requests\Student\ImportStudentsRequest;
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
}
