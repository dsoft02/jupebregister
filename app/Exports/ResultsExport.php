<?php

namespace App\Exports;

use App\Models\Result;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ResultsExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(
        private readonly array $filters = [],
    ) {}

    public function collection(): Collection
    {
        return Result::query()
            ->with('student.subjectOne', 'student.subjectTwo', 'student.subjectThree')
            ->when(isset($this->filters['status']) && $this->filters['status'],
                fn ($q) => $q->where('status', $this->filters['status'])
            )
            ->when(isset($this->filters['session']) && $this->filters['session'],
                fn ($q) => $q->where('session', $this->filters['session'])
            )
            ->get()
            ->map(fn (Result $result) => [
                'foundation_number' => $result->student->foundation_number,
                'examination_number' => $result->student->examination_number,
                'surname' => $result->student->surname,
                'first_name' => $result->student->first_name,
                'middle_name' => $result->student->middle_name,
                'subject_one' => $result->subject_one,
                'grade_one' => $result->grade_one->value,
                'subject_two' => $result->subject_two,
                'grade_two' => $result->grade_two->value,
                'subject_three' => $result->subject_three,
                'grade_three' => $result->grade_three->value,
                'total_point' => $result->total_point,
                'bonus_point' => $result->bonus_point,
                'status' => $result->status->label(),
                'session' => $result->session,
            ]);
    }

    public function headings(): array
    {
        return [
            'foundation_number',
            'examination_number',
            'surname',
            'first_name',
            'middle_name',
            'subject_one',
            'grade_one',
            'subject_two',
            'grade_two',
            'subject_three',
            'grade_three',
            'total_point',
            'bonus_point',
            'status',
            'session',
        ];
    }
}
