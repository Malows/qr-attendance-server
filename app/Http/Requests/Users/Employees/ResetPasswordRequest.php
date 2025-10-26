<?php

namespace App\Http\Requests\Users\Employees;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $employee = $this->route('employee');

        return $this->user()->can('update', $employee);
    }

    public function rules(): array
    {
        return [
            // No additional parameters needed
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
