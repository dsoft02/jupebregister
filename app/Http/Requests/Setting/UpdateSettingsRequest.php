<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'letterhead_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'official_stamp' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'director_signature' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'director_name' => ['nullable', 'string', 'max:255'],
            'director_credentials' => ['nullable', 'string', 'max:255'],
            'vice_chancellor_name' => ['nullable', 'string', 'max:255'],
            'vice_chancellor_credentials' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'current_session' => ['nullable', 'string', 'max:20'],
        ];
    }
}
