<?php

namespace App\Http\Requests\Users\Roles;

use Illuminate\Foundation\Http\FormRequest;

class SyncRolesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('assign-roles');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name',
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
            'roles.required' => 'Los roles son requeridos.',
            'roles.array' => 'Los roles deben ser un array.',
            'roles.*.exists' => 'Uno o más de los roles seleccionados no existen.',
        ];
    }
}