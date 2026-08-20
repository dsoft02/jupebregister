<?php

namespace App\Imports;

use App\Actions\Results\UpsertResult;
use App\Enums\ResultGrade;
use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ResultsImport implements SkipsEmptyRows, ToCollection, WithHeadingRow, WithValidation
{
    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public function collection(Collection $rows): void
    {
        $action = app(UpsertResult::class);

        foreach ($rows as $row) {
            $examinationNumber = trim($row['examination_number'] ?? '');

            if (blank($examinationNumber)) {
                $this->skipped++;

                continue;
            }

            $student = Student::where('examination_number', $examinationNumber)->first();

            if (! $student) {
                $this->skipped++;

                continue;
            }

            $gradeOne = strtoupper(trim($row['grade_one'] ?? ''));
            $gradeTwo = strtoupper(trim($row['grade_two'] ?? ''));
            $gradeThree = strtoupper(trim($row['grade_three'] ?? ''));

            if (! ResultGrade::tryFrom($gradeOne) || ! ResultGrade::tryFrom($gradeTwo) || ! ResultGrade::tryFrom($gradeThree)) {
                $this->skipped++;

                continue;
            }

            $existingResult = $student->currentResult();

            $data = [
                'subject_one' => $student->subjectOne?->name ?? '',
                'grade_one' => $gradeOne,
                'subject_two' => $student->subjectTwo?->name ?? '',
                'grade_two' => $gradeTwo,
                'subject_three' => $student->subjectThree?->name ?? '',
                'grade_three' => $gradeThree,
            ];

            $action->run($student->id, $data);

            if ($existingResult) {
                $this->updated++;
            } else {
                $this->created++;
            }
        }
    }

    public function rules(): array
    {
        return [
            'examination_number' => ['required', 'string'],
            'grade_one' => ['required', 'string', 'in:A,B,C,D,E,F,X,Q,W'],
            'grade_two' => ['required', 'string', 'in:A,B,C,D,E,F,X,Q,W'],
            'grade_three' => ['required', 'string', 'in:A,B,C,D,E,F,X,Q,W'],
        ];
    }
}
