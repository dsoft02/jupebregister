<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case ProgrammeOfficer = 'programme_officer';
    case Director = 'director';
    case Student = 'student';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::ProgrammeOfficer => 'Programme Officer',
            self::Director => 'Director',
            self::Student => 'Student',
        };
    }
}
