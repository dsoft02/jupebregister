<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private readonly array $filters = [],
    ) {}

    public function collection(): Collection
    {
        return Student::query()
            ->with('subjectOne', 'subjectTwo', 'subjectThree')
            ->when(isset($this->filters['session']) && $this->filters['session'],
                fn ($q) => $q->where('session', $this->filters['session'])
            )
            ->when(isset($this->filters['status']) && $this->filters['status'],
                fn ($q) => $q->where('status', $this->filters['status'])
            )
            ->when(isset($this->filters['subject']) && $this->filters['subject'],
                fn ($q) => $q->where('subject_one_id', $this->filters['subject'])
                    ->orWhere('subject_two_id', $this->filters['subject'])
                    ->orWhere('subject_three_id', $this->filters['subject'])
            )
            ->get()
            ->map(fn (Student $student) => [
                'surname' => $student->surname,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'foundation_number' => $student->foundation_number,
                'jupeb_number' => $student->jupeb_number,
                'examination_number' => $student->examination_number,
                'subject_one' => $student->subjectOne?->name,
                'subject_two' => $student->subjectTwo?->name,
                'subject_three' => $student->subjectThree?->name,
                'session' => $student->session,
                'status' => $student->status->label(),
                'phone' => $student->phone,
                'email' => $student->email,
            ]);
    }

    public function headings(): array
    {
        return [
            'surname',
            'first_name',
            'middle_name',
            'foundation_number',
            'jupeb_number',
            'examination_number',
            'subject_one',
            'subject_two',
            'subject_three',
            'session',
            'status',
            'phone',
            'email',
        ];
    }
}
