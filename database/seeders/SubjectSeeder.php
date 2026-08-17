<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            'Biology',
            'Chemistry',
            'Physics',
            'Mathematics',
            'Economics',
            'Government',
            'Literature in English',
            'Christian Religious Studies',
            'Yoruba',
            'History',
            'Geography',
            'Agricultural Science',
        ];

        foreach ($subjects as $name) {
            Subject::firstOrCreate(['name' => $name]);
        }
    }
}
