<?php

namespace App\Actions\Students;

use App\Actions\Logs\LogActivity;
use App\Models\Student;
use App\Services\StudentAccountService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateStudent
{
    public function __construct(
        private readonly LogActivity $log,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function run(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data) {
            $passport = Arr::pull($data, 'passport');

            if ($passport instanceof \Illuminate\Http\UploadedFile) {
                $this->deleteStoredFile($student->passport);
                $data['passport'] = $passport->store('passports', 'public');
            }

            $student->update($data);

            app(StudentAccountService::class)->syncIdentifier($student);

            $this->log->run(
                action: 'student.updated',
                description: 'Student "'.$student->fullName().'" ('.$student->foundation_number.') was updated',
                modelType: Student::class,
                modelId: $student->id,
            );

            return $student;
        });
    }

    public function deleteStoredFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
