<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Student, $this>
     */
    public function studentsAsSubjectOne(): HasMany
    {
        return $this->hasMany(Student::class, 'subject_one_id');
    }

    /**
     * @return HasMany<Student, $this>
     */
    public function studentsAsSubjectTwo(): HasMany
    {
        return $this->hasMany(Student::class, 'subject_two_id');
    }

    /**
     * @return HasMany<Student, $this>
     */
    public function studentsAsSubjectThree(): HasMany
    {
        return $this->hasMany(Student::class, 'subject_three_id');
    }

    public function totalStudents(): int
    {
        return $this->studentsAsSubjectOne()->count()
            + $this->studentsAsSubjectTwo()->count()
            + $this->studentsAsSubjectThree()->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
