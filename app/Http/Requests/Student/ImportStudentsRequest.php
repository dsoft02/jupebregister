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
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls,txt', 'max:5120'],
            'update_existing' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please choose a spreadsheet to import.',
            'file.mimes' => 'The file must be a CSV or Excel (xlsx/xls) document.',
        ];
    }
}
