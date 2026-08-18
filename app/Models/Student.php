<?php

namespace App\Models;

use App\Enums\StudentStatus;
use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'surname',
        'first_name',
        'middle_name',
        'foundation_number',
        'examination_number',
        'subject_one_id',
        'subject_two_id',
        'subject_three_id',
        'passport',
        'status',
        'registered_at',
    ];

    public function getSessionAttribute(): string
    {
        return app(SettingsService::class)->get('current_session') ?? now()->format('Y').'/'.(now()->format('Y') + 1);
    }

    protected function casts(): array
    {
        return [
            'status' => StudentStatus::class,
            'registered_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subjectOne(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_one_id');
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subjectTwo(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_two_id');
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subjectThree(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_three_id');
    }

    /**
     * Get the student's three chosen subjects as an array.
     *
     * @return array<int, Subject>
     */
    public function chosenSubjects(): array
    {
        return array_filter([$this->subjectOne, $this->subjectTwo, $this->subjectThree]);
    }

    /**
     * Get the names of the student's chosen subjects.
     *
     * @return array<int, string>
     */
    public function chosenSubjectNames(): array
    {
        return array_map(fn (Subject $s) => $s->name, $this->chosenSubjects());
    }

    /**
     * @return HasOne<Result, $this>
     */
    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }

    public function fullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->surname,
            $this->first_name,
            $this->middle_name,
        ])));
    }

    public function lastNameFirst(): string
    {
        return $this->surname.', '.trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
        ])));
    }

    public function initials(): string
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->surname]);
        $initials = '';
        foreach ($parts as $part) {
            $initials .= strtoupper(mb_substr($part, 0, 1));
        }

        return $initials;
    }

    public function scopeApproved($query)
    {
        return $query->where('status', StudentStatus::Approved->value);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('surname', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('middle_name', 'like', "%{$term}%")
                ->orWhere('foundation_number', 'like', "%{$term}%")
                ->orWhere('examination_number', 'like', "%{$term}%");
        });
    }
}
