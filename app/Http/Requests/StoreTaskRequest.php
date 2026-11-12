<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    return [
        'project_id' => ['required', 'exists:projects,id'],
        'title'=> ['required', 'string', 'max:255'],
        'status' => ['in:todo'],
        'due_date' => ['nullable', 'date', 'after_or_equal:today'],
];
}

}
