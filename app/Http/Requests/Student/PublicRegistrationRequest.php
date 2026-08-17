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
            'examination_number' => ['required', 'string', 'max:50', Rule::unique('students', 'examination_number')],
            'subject_one_id' => ['required', 'exists:subjects,id'],
            'subject_two_id' => ['required', 'exists:subjects,id'],
            'subject_three_id' => ['required', 'exists:subjects,id'],
            'passport' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $subjects = array_filter([
                $this->subject_one_id,
                $this->subject_two_id,
                $this->subject_three_id,
            ]);

            if (count($subjects) !== count(array_unique($subjects))) {
                $validator->errors()->add('subjects', 'Each subject must be unique. Please select three different subjects.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'foundation_number.unique' => 'This Foundation Number has already been registered.',
            'examination_number.unique' => 'This Examination Number has already been registered.',
            'passport.required' => 'Please upload a passport photo.',
            'passport.image' => 'The passport photo must be an image.',
            'passport.max' => 'The passport photo may not be larger than 500KB.',
        ];
    }
}
