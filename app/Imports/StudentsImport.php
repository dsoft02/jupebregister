<?php

namespace App\Imports;

use App\Actions\Students\CreateStudent;
use App\Models\Student;
use App\Models\Subject;
use App\Services\SettingsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements SkipsEmptyRows, ToCollection, WithHeadingRow, WithValidation
{
    private const REQUIRED_COLUMNS = [
        'surname',
        'first_name',
        'foundation_number',
        'subject_one',
        'subject_two',
        'subject_three',
    ];

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
            $subjectOne = $this->resolveSubject($row['subject_one'] ?? null);
            $subjectTwo = $this->resolveSubject($row['subject_two'] ?? null);
            $subjectThree = $this->resolveSubject($row['subject_three'] ?? null);

            if (! $subjectOne || ! $subjectTwo || ! $subjectThree) {
                $this->skipped++;

                continue;
            }

            $data = [
                'surname' => Str::title($row['surname']),
                'first_name' => Str::title($row['first_name']),
                'middle_name' => isset($row['middle_name']) ? Str::title($row['middle_name']) : null,
                'foundation_number' => trim($row['foundation_number']),
                'examination_number' => isset($row['examination_number']) ? trim($row['examination_number']) : null,
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

    public function rules(): array
    {
        $foundationLength = app(SettingsService::class)->foundationNumberLength();

        return [
            'surname' => ['required', 'string'],
            'first_name' => ['required', 'string'],
            'foundation_number' => ['required', 'string', 'size:'.$foundationLength],
            'subject_one' => ['required', 'string'],
            'subject_two' => ['required', 'string'],
            'subject_three' => ['required', 'string'],
        ];
    }

    private function resolveSubject(?string $name): ?Subject
    {
        if (blank($name)) {
            return null;
        }

        $name = trim($name);

        return Subject::withTrashed()->where('name', $name)->first()
            ?? Subject::where('name', 'like', "%{$name}%")->first();
    }
}
