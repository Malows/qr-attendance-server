<?php

namespace App\Http\Requests\Users\Employees;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $employee = $this->route('employee');

        return $this->user()->can('update', $employee);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employee = $this->route('employee');

        return [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:employees,email,'.$employee->id,
            'phone' => 'nullable|string|max:20',
            'employee_code' => 'sometimes|required|string|unique:employees,employee_code,'.$employee->id,
            'password' => 'nullable|string|min:8',
            'hire_date' => 'nullable|date',
            'position' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
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
            'first_name.required' => 'El nombre es obligatorio cuando se proporciona.',
            'first_name.string' => 'El nombre debe ser texto.',
            'first_name.max' => 'El nombre no puede exceder 255 caracteres.',
            'last_name.required' => 'El apellido es obligatorio cuando se proporciona.',
            'last_name.string' => 'El apellido debe ser texto.',
            'last_name.max' => 'El apellido no puede exceder 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio cuando se proporciona.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'El correo electrónico ya está en uso.',
            'phone.string' => 'El teléfono debe ser texto.',
            'phone.max' => 'El teléfono no puede exceder 20 caracteres.',
            'employee_code.required' => 'El código de empleado es obligatorio cuando se proporciona.',
            'employee_code.string' => 'El código de empleado debe ser texto.',
            'employee_code.unique' => 'El código de empleado ya está en uso.',
            'password.string' => 'La contraseña debe ser texto.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'hire_date.date' => 'La fecha de contratación debe ser una fecha válida.',
            'position.string' => 'El puesto debe ser texto.',
            'position.max' => 'El puesto no puede exceder 255 caracteres.',
            'notes.string' => 'Las notas deben ser texto.',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres.',
            'is_active.boolean' => 'El estado activo debe ser verdadero o falso.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'nombre',
            'last_name' => 'apellido',
            'email' => 'correo electrónico',
            'phone' => 'teléfono',
            'employee_code' => 'código de empleado',
            'password' => 'contraseña',
            'hire_date' => 'fecha de contratación',
            'position' => 'puesto',
            'notes' => 'notas',
            'is_active' => 'estado activo',
        ];
    }
}
