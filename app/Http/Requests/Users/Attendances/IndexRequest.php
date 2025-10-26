<?php

namespace App\Http\Requests\Users\Attendances;

use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow if user has permission to view attendances
        return $this->user()->can('viewAny', Attendance::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|integer|exists:employees,id',
            'location_id' => 'nullable|integer|exists:locations,id',
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
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
            'employee_id.integer' => 'El ID del empleado debe ser un número entero.',
            'employee_id.exists' => 'El empleado seleccionado no existe.',
            'location_id.integer' => 'El ID de la locación debe ser un número entero.',
            'location_id.exists' => 'La locación seleccionada no existe.',
            'start_date.date' => 'La fecha de inicio debe ser una fecha válida.',
            'start_date.date_format' => 'La fecha de inicio debe tener el formato YYYY-MM-DD.',
            'end_date.date' => 'La fecha de fin debe ser una fecha válida.',
            'end_date.date_format' => 'La fecha de fin debe tener el formato YYYY-MM-DD.',
            'end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
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
            'employee_id' => 'empleado',
            'location_id' => 'locación',
            'start_date' => 'fecha de inicio',
            'end_date' => 'fecha de fin',
        ];
    }
}
