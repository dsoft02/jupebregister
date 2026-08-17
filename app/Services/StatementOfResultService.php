<?php

namespace App\Services;

use App\Models\Result;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class StatementOfResultService
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    /**
     * Render the official Statement of Result PDF for a student.
     *
     * The uploaded letterhead image is embedded as the full-page background;
     * only the dynamic fields are overlaid on top of it.
     */
    public function download(Result $result, bool $stream = true): \Symfony\Component\HttpFoundation\Response
    {
        $student = $result->student->load('subjectOne', 'subjectTwo', 'subjectThree');

        $passport = $this->studentPassportDataUri($student);

        $pdf = Pdf::loadView('pdf.statement-of-result', [
            'result' => $result,
            'student' => $student,
            'settings' => $this->settings,
            'letterhead' => $this->settings->fileAsDataUri('letterhead_image'),
            'stamp' => $this->settings->fileAsDataUri('official_stamp'),
            'signature' => $this->settings->fileAsDataUri('director_signature'),
            'passport' => $passport,
            'issueDate' => $this->issueDate(),
        ])
            ->setPaper('a4', 'portrait');

        return $stream
            ? $pdf->stream($this->filename($student))
            : $pdf->download($this->filename($student));
    }

    /**
     * A readable file name following the official slip naming convention.
     */
    public function filename(Student $student): string
    {
        $reference = str_replace(['/', '\\'], '-', $student->foundation_number);

        return 'Statement-of-Result_'.str_replace(' ', '_', $student->fullName())
            .'_'.$reference.'.pdf';
    }

    /**
     * Resolve the issue date shown on the slip: the configured default, or today.
     */
    public function issueDate(): string
    {
        $date = $this->settings->get('issue_date');

        if (blank($date)) {
            return Carbon::now()->format('d/m/Y');
        }

        return Carbon::parse($date)->format('d/m/Y');
    }

    /**
     * Embed the student's passport as a base64 data-URI so the PDF is fully
     * self-contained and does not depend on the web server.
     */
    private function studentPassportDataUri(Student $student): ?string
    {
        if (blank($student->passport)) {
            return null;
        }

        $path = Storage::disk('public')->path($student->passport);

        if (! file_exists($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }
}
