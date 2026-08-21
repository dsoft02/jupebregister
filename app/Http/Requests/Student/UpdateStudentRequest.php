<?php

namespace App\Http\Requests\Student;

use App\Services\SettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');
        $settings = app(SettingsService::class);

        return [
            'surname' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'foundation_number' => ['required', 'string', 'size:'.$settings->foundationNumberLength(), Rule::unique('students', 'foundation_number')->ignore($student->id)],
            'examination_number' => ['required', 'string', 'size:'.$settings->examinationNumberLength(), Rule::unique('students', 'examination_number')->ignore($student->id)],
            'subject_one_id' => ['required', 'exists:subjects,id'],
            'subject_two_id' => ['required', 'exists:subjects,id', 'different:subject_one_id'],
            'subject_three_id' => ['required', 'exists:subjects,id', 'different:subject_one_id', 'different:subject_two_id'],
            'passport' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:500'],
            'session' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ];
    }
}
