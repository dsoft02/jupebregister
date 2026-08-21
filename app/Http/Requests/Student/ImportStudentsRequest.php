<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class ImportStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'students_file' => ['required', 'file', 'extensions:csv,xlsx,xls', 'max:5120'],
            'update_existing' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'students_file.required' => 'Please choose a spreadsheet to import.',
            'students_file.extensions' => 'The file must be a CSV or Excel (xlsx/xls) document.',
        ];
    }

    public function attributes(): array
    {
        return [
            'students_file' => 'file',
        ];
    }
}
