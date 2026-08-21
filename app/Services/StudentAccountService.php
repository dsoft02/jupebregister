<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentAccountService
{
    /**
     * The default password given to every student portal account.
     */
    public const DEFAULT_PASSWORD = 'password';

    /**
     * Get or create the portal account linked to a student.
     */
    public function ensureFor(Student $student): User
    {
        $user = $student->user()->first();

        if ($user) {
            return $user;
        }

        return DB::transaction(function () use ($student) {
            $user = User::firstOrCreate(
                ['student_id' => $student->id],
                [
                    'name' => $student->fullName(),
                    'email' => $this->emailFor($student),
                    'password' => self::DEFAULT_PASSWORD,
                ],
            );

            if (! $user->hasRole(UserRole::Student->value)) {
                $user->assignRole(UserRole::Student->value);
            }

            return $user;
        });
    }

    /**
     * Keep the linked account's name and generated email in sync after a
     * student changes their details (e.g. a new foundation number).
     */
    public function syncIdentifier(Student $student): void
    {
        $user = $student->user()->first();

        if (! $user) {
            return;
        }

        $user->update([
            'name' => $student->fullName(),
            'email' => $this->emailFor($student),
        ]);
    }

    /**
     * A deterministic, unique email derived from the foundation number.
     * It is only used internally — students sign in with the number itself.
     */
    private function emailFor(Student $student): string
    {
        $base = Str::slug($student->foundation_number).'@students.paau.edu.ng';

        $taken = User::where('email', $base)
            ->where(fn ($q) => $q->whereNull('student_id')->orWhere('student_id', '!=', $student->id))
            ->exists();

        return $taken ? Str::slug($student->foundation_number).'.'.$student->id.'@students.paau.edu.ng' : $base;
    }
}
