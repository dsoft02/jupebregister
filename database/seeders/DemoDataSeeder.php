<?php

namespace Database\Seeders;

use App\Actions\Results\UpsertResult;
use App\Enums\ResultStatus;
use App\Enums\StudentStatus;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = Subject::all()->keyBy('name');

        $students = [
            ['surname' => 'Adeyemi', 'first_name' => 'Oluwaseun', 'middle_name' => 'Temitope', 'foundation_number' => 'PAAU/FS/25/001', 'examination_number' => 'EXM000001'],
            ['surname' => 'Okonkwo', 'first_name' => 'Chiamaka', 'middle_name' => 'Blessing', 'foundation_number' => 'PAAU/FS/25/002', 'examination_number' => 'EXM000002'],
            ['surname' => 'Bello', 'first_name' => 'Abdulrahman', 'middle_name' => 'Sodiq', 'foundation_number' => 'PAAU/FS/25/003', 'examination_number' => 'EXM000003'],
            ['surname' => 'Eze', 'first_name' => 'Ngozi', 'middle_name' => null, 'foundation_number' => 'PAAU/FS/25/004', 'examination_number' => 'EXM000004'],
            ['surname' => 'Okafor', 'first_name' => 'Ifeanyi', 'middle_name' => 'David', 'foundation_number' => 'PAAU/FS/25/005', 'examination_number' => 'EXM000005'],
            ['surname' => 'Mohammed', 'first_name' => 'Aisha', 'middle_name' => 'Fatima', 'foundation_number' => 'PAAU/FS/25/006', 'examination_number' => 'EXM000006'],
            ['surname' => 'Adeleke', 'first_name' => 'Kemi', 'middle_name' => 'Olajumoke', 'foundation_number' => 'PAAU/FS/25/007', 'examination_number' => 'EXM000007'],
            ['surname' => 'Ibrahim', 'first_name' => 'Musa', 'middle_name' => null, 'foundation_number' => 'PAAU/FS/25/008', 'examination_number' => 'EXM000008'],
            ['surname' => 'Nwachukwu', 'first_name' => 'Adaeze', 'middle_name' => 'Chinenye', 'foundation_number' => 'PAAU/FS/25/009', 'examination_number' => 'EXM000009'],
            ['surname' => 'Yusuf', 'first_name' => 'Habiba', 'middle_name' => 'Amina', 'foundation_number' => 'PAAU/FS/25/010', 'examination_number' => 'EXM000010'],
        ];

        $subjectSets = [
            ['Biology', 'Chemistry', 'Physics'],
            ['Economics', 'Government', 'Literature in English'],
            ['Physics', 'Chemistry', 'Mathematics'],
            ['Biology', 'Chemistry', 'Agricultural Science'],
            ['Economics', 'Government', 'Christian Religious Studies'],
            ['Mathematics', 'Physics', 'Geography'],
            ['Literature in English', 'Government', 'History'],
            ['Biology', 'Agricultural Science', 'Chemistry'],
            ['Economics', 'Mathematics', 'Geography'],
            ['Government', 'History', 'Yoruba'],
        ];

        // Grade sets demonstrating the scoring rules (A=5 ... E=1, bonus only when all pass).
        $gradeSets = [
            ['D', 'D', 'E'], // 2+2+1+1 = 6
            ['A', 'A', 'A'], // 5+5+5+1 = 16
            ['A', 'B', 'F'], // 5+4+0+0 = 9
            ['B', 'B', 'C'], // 4+4+3+1 = 12
            ['A', 'A', 'B'], // 5+5+4+1 = 15
            ['C', 'C', 'D'], // 3+3+2+1 = 9
            ['B', 'C', 'C'], // 4+3+3+1 = 11
            ['A', 'C', 'B'], // 5+3+4+1 = 13
            ['E', 'E', 'E'], // 1+1+1+1 = 4
            ['A', 'B', 'X'], // 5+4+0+0 = 9
        ];

        $upsert = app(UpsertResult::class);

        foreach ($students as $index => $data) {
            [$s1Name, $s2Name, $s3Name] = $subjectSets[$index];
            $subjectOne = $subjects[$s1Name];
            $subjectTwo = $subjects[$s2Name];
            $subjectThree = $subjects[$s3Name];

            $student = Student::firstOrCreate(
                ['foundation_number' => $data['foundation_number']],
                [
                    ...$data,
                    'subject_one_id' => $subjectOne->id,
                    'subject_two_id' => $subjectTwo->id,
                    'subject_three_id' => $subjectThree->id,
                    'status' => StudentStatus::Approved,
                    'registered_at' => now()->subMonths(3),
                ],
            );

            // Leave a couple of students without results and a couple pending.
            if (in_array($index, [8, 9])) {
                continue;
            }

            [$g1, $g2, $g3] = $gradeSets[$index];

            $publish = ! in_array($index, [6, 7]);

            $result = $upsert->run($student->id, [
                'subject_one' => $s1Name,
                'subject_two' => $s2Name,
                'subject_three' => $s3Name,
                'grade_one' => $g1,
                'grade_two' => $g2,
                'grade_three' => $g3,
                'status' => $publish ? ResultStatus::Published->value : ResultStatus::Draft->value,
            ]);

            if ($publish) {
                $result->update(['published_at' => now()->subDays(rand(5, 30))]);
            }
        }
    }
}
