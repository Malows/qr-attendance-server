<?php

namespace App\Http\Requests\Employees;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('employee')->check();
    }

    public function rules(): array
    {
        $employee = $this->user('employee');
        
        return [
            'current_password' => $employee->force_password_change ? 'nullable' : 'required|string|current_password:employee',
            'new_password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'La contraseña actual es requerida.',
            'current_password.current_password' => 'La contraseña actual es incorrecta.',
            'new_password.required' => 'La nueva contraseña es requerida.',
            'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'new_password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ];
    }
}
