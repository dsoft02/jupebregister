<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicRegistrationRequest extends FormRequest
{
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
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'foundation_number.unique' => 'This Foundation Number has already been registered.',
            'jupeb_number.unique' => 'This JUPEB Number has already been registered.',
            'examination_number.unique' => 'This Examination Number has already been registered.',
            'passport.image' => 'The passport photo must be an image.',
            'passport.max' => 'The passport photo may not be larger than 2MB.',
        ];
    }
}
