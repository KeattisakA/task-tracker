<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['sometimes', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['in:todo'],
            'description' => ['nullable', 'string'],
        ];
    }
}