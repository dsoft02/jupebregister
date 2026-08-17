<?php

namespace Tests\Unit;

use App\Enums\ResultGrade;
use App\Services\GradeService;
use PHPUnit\Framework\TestCase;

class GradeServiceTest extends TestCase
{
    private GradeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new GradeService();
    }

    public function test_grade_point_scale_matches_jupeb_system(): void
    {
        $this->assertSame(5, $this->service->pointsFor(ResultGrade::A));
        $this->assertSame(4, $this->service->pointsFor(ResultGrade::B));
        $this->assertSame(3, $this->service->pointsFor(ResultGrade::C));
        $this->assertSame(2, $this->service->pointsFor(ResultGrade::D));
        $this->assertSame(1, $this->service->pointsFor(ResultGrade::E));
        $this->assertSame(0, $this->service->pointsFor(ResultGrade::F));
        $this->assertSame(0, $this->service->pointsFor(ResultGrade::X));
        $this->assertSame(0, $this->service->pointsFor(ResultGrade::Q));
        $this->assertSame(0, $this->service->pointsFor(ResultGrade::W));
    }

    public function test_d_d_e_equals_six(): void
    {
        $result = $this->service->calculate('D', 'D', 'E');

        $this->assertSame(6, $result['total_point']);
        $this->assertSame(1, $result['bonus_point']);
    }

    public function test_a_a_a_equals_sixteen_maximum(): void
    {
        $result = $this->service->calculate('A', 'A', 'A');

        $this->assertSame(16, $result['total_point']);
        $this->assertSame(1, $result['bonus_point']);
    }

    public function test_a_b_f_equals_nine_no_bonus(): void
    {
        $result = $this->service->calculate('A', 'B', 'F');

        $this->assertSame(9, $result['total_point']);
        $this->assertSame(0, $result['bonus_point']);
    }

    public function test_bonus_only_when_all_subjects_passed(): void
    {
        // Absent grade must not earn the bonus.
        $result = $this->service->calculate('A', 'B', 'X');
        $this->assertSame(0, $result['bonus_point']);
        $this->assertSame(9, $result['total_point']);

        // Cancelled grade must not earn the bonus.
        $result = $this->service->calculate('A', 'B', 'Q');
        $this->assertSame(0, $result['bonus_point']);

        // Withheld grade must not earn the bonus.
        $result = $this->service->calculate('A', 'B', 'W');
        $this->assertSame(0, $result['bonus_point']);
    }

    public function test_total_cannot_exceed_sixteen(): void
    {
        $result = $this->service->calculate('A', 'A', 'A');

        $this->assertLessThanOrEqual(16, $result['total_point']);
    }
}
