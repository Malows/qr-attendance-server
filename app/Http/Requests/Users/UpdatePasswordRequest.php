<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated to update their password
        return $this->user('api') !== null;
    }

    public function rules(): array
    {
        $user = $this->user('api');
        
        return [
            'current_password' => $user->force_password_change ? 'nullable' : 'required|string|current_password:api',
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
