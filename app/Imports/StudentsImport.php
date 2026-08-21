<?php

namespace App\Imports;

use App\Actions\Students\CreateStudent;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements SkipsEmptyRows, ToCollection, WithCustomCsvSettings, WithHeadingRow
{
    public function __construct(
        private readonly bool $updateExisting = false,
    ) {}

    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public function collection(Collection $rows): void
    {
        $action = app(CreateStudent::class);

        foreach ($rows as $row) {
            if (blank($row['surname'] ?? null) || blank($row['first_name'] ?? null)) {
                $this->skipped++;

                continue;
            }

            $foundationNumber = $this->normalize($row['foundation_number'] ?? null);

            if (blank($foundationNumber)) {
                $this->skipped++;

                continue;
            }

            $subjectOne = $this->resolveSubject($row['subject_one'] ?? null);
            $subjectTwo = $this->resolveSubject($row['subject_two'] ?? null);
            $subjectThree = $this->resolveSubject($row['subject_three'] ?? null);

            if (! $subjectOne || ! $subjectTwo || ! $subjectThree) {
                $this->skipped++;

                continue;
            }

            $data = [
                'surname' => Str::title($this->normalize($row['surname'])),
                'first_name' => Str::title($this->normalize($row['first_name'])),
                'middle_name' => filled($row['middle_name'] ?? null)
                    ? Str::title($this->normalize($row['middle_name']))
                    : null,
                'foundation_number' => $foundationNumber,
                'examination_number' => filled($row['examination_number'] ?? null)
                    ? $this->normalize($row['examination_number'])
                    : null,
                'subject_one_id' => $subjectOne->id,
                'subject_two_id' => $subjectTwo->id,
                'subject_three_id' => $subjectThree->id,
            ];

            $existing = Student::withTrashed()
                ->where('foundation_number', $data['foundation_number'])
                ->first();

            if ($existing) {
                if (! $this->updateExisting) {
                    $this->skipped++;

                    continue;
                }

                if ($existing->trashed()) {
                    $existing->restore();
                }

                $existing->update($data);
                $this->updated++;

                continue;
            }

            $action->run($data, public: false);
            $this->created++;
        }
    }

    private function resolveSubject(mixed $name): ?Subject
    {
        if (blank($name)) {
            return null;
        }

        $name = $this->normalize($name);

        return Subject::withTrashed()->where('name', $name)->first()
            ?? Subject::where('name', 'like', "%{$name}%")->first();
    }

    private function normalize(mixed $value): string
    {
        return trim(is_string($value) ? $value : (string) ($value ?? ''));
    }

    public function getCsvSettings(): array
    {
        return ['delimiter' => ','];
    }
}
