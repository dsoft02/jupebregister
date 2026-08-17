<?php

namespace App\Http\Requests\Student;

use App\Enums\ResultGrade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    /**
     * Authorization handled by the controller middleware / policies.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'surname' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'foundation_number' => ['required', 'string', 'max:50', Rule::unique('students', 'foundation_number')],
            'jupeb_number' => ['nullable', 'string', 'max:50', Rule::unique('students', 'jupeb_number')],
            'examination_number' => ['nullable', 'string', 'max:50', Rule::unique('students', 'examination_number')],
            'subject_one_id' => ['required', 'exists:subjects,id'],
            'subject_two_id' => ['required', 'exists:subjects,id'],
            'subject_three_id' => ['required', 'exists:subjects,id'],
            'passport' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'session' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'in:pending,approved,rejected'],
        ];
    }
}
