<?php

namespace App\Http\Requests\Employees;

use Illuminate\Foundation\Http\FormRequest;

class IndexEmployeeAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Employee can always view their own attendances
        return $this->user('employee') !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
            'location_id' => 'nullable|integer|exists:locations,id',
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
            'start_date.date' => 'La fecha de inicio debe ser una fecha válida.',
            'start_date.date_format' => 'La fecha de inicio debe tener el formato YYYY-MM-DD.',
            'end_date.date' => 'La fecha de fin debe ser una fecha válida.',
            'end_date.date_format' => 'La fecha de fin debe tener el formato YYYY-MM-DD.',
            'end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'location_id.integer' => 'El ID de la locación debe ser un número entero.',
            'location_id.exists' => 'La locación seleccionada no existe.',
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
            'start_date' => 'fecha de inicio',
            'end_date' => 'fecha de fin',
            'location_id' => 'locación',
        ];
    }
}
