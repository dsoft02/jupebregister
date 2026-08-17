<?php

namespace App\Http\Requests\Result;

use App\Enums\ResultGrade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_one' => ['required', 'string', 'max:255'],
            'grade_one' => ['required', Rule::enum(ResultGrade::class)],
            'subject_two' => ['required', 'string', 'max:255'],
            'grade_two' => ['required', Rule::enum(ResultGrade::class)],
            'subject_three' => ['required', 'string', 'max:255'],
            'grade_three' => ['required', Rule::enum(ResultGrade::class)],
            'status' => ['nullable', 'in:draft,published'],
        ];
    }
}
