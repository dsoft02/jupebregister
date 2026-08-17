<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('students.view');
    }

    public function view(User $user, Student $student): bool
    {
        return $user->can('students.view');
    }

    public function create(User $user): bool
    {
        return $user->can('students.create');
    }

    public function update(User $user, Student $student): bool
    {
        return $user->can('students.edit');
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->can('students.delete');
    }

    public function import(User $user): bool
    {
        return $user->can('students.import');
    }

    public function export(User $user): bool
    {
        return $user->can('students.export');
    }
}
