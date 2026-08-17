<?php

namespace App\Enums;

enum ResultGrade: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case E = 'E';
    case F = 'F';
    case X = 'X';
    case Q = 'Q';
    case W = 'W';

    /**
     * JUPEB grading scale used to convert a grade letter into points.
     */
    public function points(): int
    {
        return match ($this) {
            self::A => 5,
            self::B => 4,
            self::C => 3,
            self::D => 2,
            self::E => 1,
            default => 0,
        };
    }

    /**
     * A subject is considered "passed" only for A–E.
     */
    public function isPassed(): bool
    {
        return match ($this) {
            self::A, self::B, self::C, self::D, self::E => true,
            default => false,
        };
    }

    /**
     * Description rendered on the Statement of Result.
     */
    public function description(): string
    {
        return match ($this) {
            self::A => 'Excellent',
            self::B => 'Very Good',
            self::C => 'Good',
            self::D => 'Average',
            self::E => 'Pass',
            self::F => 'Fail',
            self::X => 'Absent',
            self::Q => 'Cancelled',
            self::W => 'Withheld',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
