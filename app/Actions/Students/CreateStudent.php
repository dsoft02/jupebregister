<?php

namespace App\Actions\Students;

use App\Actions\Logs\LogActivity;
use App\Enums\StudentStatus;
use App\Models\Student;
use App\Services\StudentAccountService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateStudent
{
    public function __construct(
        private readonly LogActivity $log,
    ) {}

    /**
     * Create a student record. Registered through the public form the record
     * is marked Pending; when created from the admin panel it defaults to
     * Approved.
     *
     * @param  array<string, mixed>  $data
     */
    public function run(array $data, bool $public = false): Student
    {
        return DB::transaction(function () use ($data, $public) {
            $passport = Arr::pull($data, 'passport');

            $student = Student::create([
                ...$data,
                'status' => $public ? StudentStatus::Pending : ($data['status'] ?? StudentStatus::Approved),
                'registered_at' => now(),
            ]);

            if ($passport instanceof \Illuminate\Http\UploadedFile) {
                $student->update([
                    'passport' => $passport->store('passports', 'public'),
                ]);
            }

            app(StudentAccountService::class)->ensureFor($student);

            $this->log->run(
                action: $public ? 'student.registered' : 'student.created',
                description: 'Student "'.$student->fullName().'" ('.$student->foundation_number.') was '
                    .($public ? 'registered through the public portal' : 'created by an admin'),
                modelType: Student::class,
                modelId: $student->id,
                properties: ['foundation_number' => $student->foundation_number],
            );

            return $student;
        });
    }
}
