<?php

namespace App\Imports;

use App\Models\Subject;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SubjectsImport implements SkipsEmptyRows, ToCollection, WithCustomCsvSettings, WithHeadingRow
{
    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name = is_string($row['name'] ?? null) ? trim($row['name']) : '';

            if (blank($name) || mb_strlen($name) > 255) {
                $this->skipped++;

                continue;
            }

            $isActive = $this->resolveIsActive($row['is_active'] ?? null);
            $existing = Subject::withTrashed()->where('name', $name)->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }

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
                'is_active' => $isActive,
            ]);
            $this->created++;
        }
    }

    private function resolveIsActive(mixed $value): bool
    {
        if (blank($value)) {
            return true;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return filter_var(trim((string) $value), FILTER_VALIDATE_BOOLEAN);
    }

    public function getCsvSettings(): array
    {
        return ['delimiter' => ','];
    }
}
