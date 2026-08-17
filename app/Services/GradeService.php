<?php

namespace App\Services;

use App\Enums\ResultGrade;

class GradeService
{
    /**
     * Convert a single grade letter to its JUPEB point value.
     */
    public function pointsFor(ResultGrade|string $grade): int
    {
        if (is_string($grade)) {
            $grade = ResultGrade::tryFrom(strtoupper($grade));
        }

        return $grade?->points() ?? 0;
    }

    /**
     * Whether a grade counts as a pass (A–E).
     */
    public function isPassed(ResultGrade|string $grade): bool
    {
        if (is_string($grade)) {
            $grade = ResultGrade::tryFrom(strtoupper($grade));
        }

        return $grade?->isPassed() ?? false;
    }

    /**
     * Calculate total points for three grades, plus the bonus point.
     *
     * Bonus rule: award 1 extra point only when all three subjects are passed
     * (grades A–E). If any grade is F, X, Q or W no bonus is given.
     */
    public function calculate(
        ResultGrade|string $gradeOne,
        ResultGrade|string $gradeTwo,
        ResultGrade|string $gradeThree,
    ): array {
        $pointOne = $this->pointsFor($gradeOne);
        $pointTwo = $this->pointsFor($gradeTwo);
        $pointThree = $this->pointsFor($gradeThree);

        $bonus = ($this->isPassed($gradeOne) && $this->isPassed($gradeTwo) && $this->isPassed($gradeThree)) ? 1 : 0;

        return [
            'point_one' => $pointOne,
            'point_two' => $pointTwo,
            'point_three' => $pointThree,
            'bonus_point' => $bonus,
            'total_point' => $pointOne + $pointTwo + $pointThree + $bonus,
        ];
    }

    /**
     * Every possible grade letter for select inputs, in a sensible order.
     */
    public function gradeOptions(): array
    {
        return ResultGrade::cases();
    }
}
