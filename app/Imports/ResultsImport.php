<?php

namespace App\Imports;

use App\Actions\Results\UpsertResult;
use App\Enums\ResultGrade;
use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ResultsImport implements SkipsEmptyRows, ToCollection, WithCustomCsvSettings, WithHeadingRow
{
    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public function collection(Collection $rows): void
    {
        $action = app(UpsertResult::class);

        foreach ($rows as $row) {
            $examinationNumber = is_string($row['examination_number'] ?? null)
                ? trim($row['examination_number'])
                : (string) ($row['examination_number'] ?? '');

            if (blank($examinationNumber)) {
                $this->skipped++;

                continue;
            }

            $student = Student::where('examination_number', $examinationNumber)->first();

            if (! $student) {
                $this->skipped++;

                continue;
            }

            $gradeOne = $this->normalizeGrade($row['grade_one'] ?? null);
            $gradeTwo = $this->normalizeGrade($row['grade_two'] ?? null);
            $gradeThree = $this->normalizeGrade($row['grade_three'] ?? null);

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

    private function normalizeGrade(mixed $value): string
    {
        return strtoupper(trim(is_string($value) ? $value : (string) ($value ?? '')));
    }

    public function getCsvSettings(): array
    {
        return ['delimiter' => ','];
    }
}
