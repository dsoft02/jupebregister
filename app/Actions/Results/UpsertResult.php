<?php

namespace App\Actions\Results;

use App\Actions\Logs\LogActivity;
use App\Enums\ResultStatus;
use App\Models\Result;
use App\Services\GradeService;
use Illuminate\Support\Facades\DB;

class UpsertResult
{
    public function __construct(
        private readonly GradeService $grades,
        private readonly LogActivity $log,
    ) {}

    /**
     * Create or update a student's result. Only grade letters are supplied;
     * points, bonus and total are derived by the GradeService.
     *
     * @param  array<string, mixed>  $data
     */
    public function run(int $studentId, array $data): Result
    {
        return DB::transaction(function () use ($studentId, $data) {
            $calculation = $this->grades->calculate(
                $data['grade_one'],
                $data['grade_two'],
                $data['grade_three'],
            );

            $payload = [
                'student_id' => $studentId,
                'subject_one' => $data['subject_one'],
                'grade_one' => $data['grade_one'],
                'subject_two' => $data['subject_two'],
                'grade_two' => $data['grade_two'],
                'subject_three' => $data['subject_three'],
                'grade_three' => $data['grade_three'],
                ...$calculation,
                'created_by' => auth()->id(),
            ];

            $result = Result::updateOrCreate(['student_id' => $studentId], $payload);

            $result->refresh();

            if (isset($data['status']) && $data['status'] === ResultStatus::Published->value) {
                $result->update([
                    'status' => ResultStatus::Published,
                    'published_at' => $result->published_at ?? now(),
                ]);
            }

            $this->log->run(
                action: 'result.upserted',
                description: 'Result saved for student #'.$studentId.' — '
                    .$result->grade_one->value.'/'.$result->grade_two->value.'/'.$result->grade_three->value
                    .' => '.$result->total_point.' points',
                modelType: Result::class,
                modelId: $result->id,
                properties: $result->only(['total_point', 'bonus_point', 'status']),
            );

            return $result;
        });
    }

    public function publish(Result $result): Result
    {
        $result->update([
            'status' => ResultStatus::Published,
            'published_at' => $result->published_at ?? now(),
        ]);

        $this->log->run(
            action: 'result.published',
            description: 'Result for student #'.$result->student_id.' was published',
            modelType: Result::class,
            modelId: $result->id,
        );

        return $result->refresh();
    }
}
