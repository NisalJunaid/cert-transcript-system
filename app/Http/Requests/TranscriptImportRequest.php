<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TranscriptImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_shortcode' => ['required', 'string', 'max:255'],
            'course_name' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ];
    }
}
