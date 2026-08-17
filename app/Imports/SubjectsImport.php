<?php

namespace App\Imports;

use App\Models\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SubjectsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name = trim($row['name'] ?? '');

            if (blank($name)) {
                $this->skipped++;
                continue;
            }

            $existing = Subject::withTrashed()->where('name', $name)->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                $isActive = isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true;

                if ($existing->is_active !== $isActive) {
                    $existing->update(['is_active' => $isActive]);
                    $this->updated++;
                } else {
                    $this->skipped++;
                }
                continue;
            }

            Subject::create([
                'name' => $name,
                'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
            ]);
            $this->created++;
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
