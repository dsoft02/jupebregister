<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Services\SettingsService;
use App\Services\StatementOfResultService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CombinedStatementOfResultController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly StatementOfResultService $statementService,
    ) {}

    /**
     * Generate a combined statement of result for selected students.
     */
    public function selected(Request $request)
    {
        $this->authorize('viewAny', Result::class);

        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['exists:results,id'],
        ]);

        $results = Result::with('student.subjectOne', 'student.subjectTwo', 'student.subjectThree')
            ->whereIn('id', $request->ids)
            ->get();

        return $this->buildPdf($results);
    }

    /**
     * Generate a combined statement of result for all students matching filters.
     */
    public function all(Request $request)
    {
        $this->authorize('viewAny', Result::class);

        $query = Result::with('student.subjectOne', 'student.subjectTwo', 'student.subjectThree');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('session')) {
            $query->where('session', $request->session);
        }

        $results = $query->latest()->get();

        return $this->buildPdf($results);
    }

    private function buildPdf($results)
    {
        $rows = $results->map(fn ($r) => [
            'student' => $r->student,
            'result'  => $r,
        ])->values();

        $issueDate = $this->statementService->issueDate();
        $currentSession = $this->settings->get('current_session', Carbon::now()->year . '/' . (Carbon::now()->year + 1));

        $data = [
            'results'         => $rows,
            'settings'        => $this->settings,
            'issueDate'       => Carbon::createFromFormat('d/m/Y', $issueDate)->format('jS F, Y'),
            'academicSession' => $currentSession,
            'examYear'        => explode('/', $currentSession)[0],
            'letterhead'      => $this->settings->fileAsDataUri('letterhead_landscape') ?? $this->fallbackAsset('letterhead_landscape.png'),
            'watermark'       => $this->settings->fileAsDataUri('watermark_image') ?? file_get_contents(public_path('assets/jupeb/watermark.png')),
            'stamp'           => $this->settings->fileAsDataUri('official_stamp') ?? $this->fallbackAsset('stamp.png'),
            'signature'       => $this->settings->fileAsDataUri('director_signature') ?? $this->fallbackAsset('signature.png'),
            'directorName'    => $this->settings->get('director_name', 'Director'),
        ];

        $pdf = Pdf::loadView('pdf.combined-statement-of-result', $data)
            ->setPaper('a4', 'landscape');

        $pdf->setOptions([
            'defaultFont'             => 'Times New Roman',
            'isHtml5ParserEnabled'    => true,
            'isRemoteEnabled'         => true,
            'isPhpEnabled'            => false,
            'enable_css_float'        => true,
            'enable_html5_parser'     => true,
        ]);

        $session = str_replace(['/', '\\'], '-', $currentSession);

        $filename = "Combined-Statement-of-Result_{$session}_" . now()->format('Ymd-His') . '.pdf';

        return $pdf->stream($filename);
    }

    private function fallbackAsset(string $file): string
    {
        return 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('assets/jupeb/'.$file)));
    }
}
