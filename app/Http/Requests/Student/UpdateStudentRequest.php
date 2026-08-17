<?php

namespace App\Http\Requests\Student;

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

        return [
            'surname' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'foundation_number' => ['required', 'string', 'max:50', Rule::unique('students', 'foundation_number')->ignore($student->id)],
            'jupeb_number' => ['nullable', 'string', 'max:50', Rule::unique('students', 'jupeb_number')->ignore($student->id)],
            'examination_number' => ['nullable', 'string', 'max:50', Rule::unique('students', 'examination_number')->ignore($student->id)],
            'subject_one_id' => ['required', 'exists:subjects,id'],
            'subject_two_id' => ['required', 'exists:subjects,id'],
            'subject_three_id' => ['required', 'exists:subjects,id'],
            'passport' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'session' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ];
    }
}
