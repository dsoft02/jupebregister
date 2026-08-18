<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Services\StatementOfResultService;
use Illuminate\Http\Request;
use ZipArchive;

class BulkResultDownloadController extends Controller
{
    public function __invoke(Request $request, StatementOfResultService $service)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:results,id'],
        ]);

        $results = Result::with('student.subjectOne', 'student.subjectTwo', 'student.subjectThree')
            ->whereIn('id', $request->ids)
            ->get();

        $zipPath = storage_path('app/results-bulk-'.now()->timestamp.'.zip');
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create ZIP file.');
        }

        foreach ($results as $result) {
            $student = $result->student;
            $filename = $service->filename($student);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.statement-of-result', [
                'result' => $result,
                'student' => $student,
                'settings' => app(\App\Services\SettingsService::class),
                'passport' => $this->studentPassportDataUri($student),
                'issueDate' => $service->issueDate(),
            ])->setPaper('a4', 'portrait');

            $pdf->setOptions([
                'defaultFont' => 'Times New Roman',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => false,
                'enable_css_float' => true,
                'enable_html5_parser' => true,
            ]);

            $zip->addFromString($filename, $pdf->output());
        }

        $zip->close();

        return response()->download($zipPath, 'results-'.now()->format('Ymd-His').'.zip')->deleteFileAfterSend(true);
    }

    private function studentPassportDataUri($student): ?string
    {
        if (blank($student->passport)) {
            return null;
        }

        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($student->passport);

        if (! file_exists($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }
}
