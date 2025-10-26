<?php

namespace App\Http\Requests\Users\Roles;

use Illuminate\Foundation\Http\FormRequest;

class RevokePermissionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('revoke-permissions');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'permission' => 'required|string|exists:permissions,name',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'permission.required' => 'El permiso es requerido.',
            'permission.exists' => 'El permiso seleccionado no existe.',
        ];
    }
}
