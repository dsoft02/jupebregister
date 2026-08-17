<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@paau.edu.ng'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@paau.edu.ng',
                'password' => 'password',
            ],
        );
        $admin->assignRole('super_admin');

        $programmeOfficer = User::firstOrCreate(
            ['email' => 'officer@paau.edu.ng'],
            [
                'name' => 'Programme Officer',
                'email' => 'officer@paau.edu.ng',
                'password' => 'password',
            ],
        );
        $programmeOfficer->assignRole('programme_officer');

        $director = User::firstOrCreate(
            ['email' => 'director@paau.edu.ng'],
            [
                'name' => 'Director, Foundation School',
                'email' => 'director@paau.edu.ng',
                'password' => 'password',
            ],
        );
        $director->assignRole('director');

        $student = User::firstOrCreate(
            ['email' => 'student@paau.edu.ng'],
            [
                'name' => 'Student Demo',
                'email' => 'student@paau.edu.ng',
                'password' => 'password',
            ],
        );
        $student->assignRole('student');

        app(SettingsService::class)->set([
            'current_session' => '2025/2026',
            'director_name' => 'Idris Ahmed Sani (PhD.)',
            'issue_date' => null,
        ]);
    }
}
