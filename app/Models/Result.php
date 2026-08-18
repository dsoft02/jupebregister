<?php

namespace App\Models;

use App\Enums\ResultGrade;
use App\Enums\ResultStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Result extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'session',
        'subject_one',
        'grade_one',
        'point_one',
        'subject_two',
        'grade_two',
        'point_two',
        'subject_three',
        'grade_three',
        'point_three',
        'bonus_point',
        'total_point',
        'status',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'grade_one' => ResultGrade::class,
            'grade_two' => ResultGrade::class,
            'grade_three' => ResultGrade::class,
            'status' => ResultStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subjects(): array
    {
        return [
            ['subject' => $this->subject_one, 'grade' => $this->grade_one, 'point' => $this->point_one],
            ['subject' => $this->subject_two, 'grade' => $this->grade_two, 'point' => $this->point_two],
            ['subject' => $this->subject_three, 'grade' => $this->grade_three, 'point' => $this->point_three],
        ];
    }

    public function maximumPoints(): int
    {
        return 16;
    }

    public function gradePointLabel(): string
    {
        return $this->total_point.'/'.$this->maximumPoints();
    }

    public function scopePublished($query)
    {
        return $query->where('status', ResultStatus::Published->value);
    }
}
