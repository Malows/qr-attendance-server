<?php

namespace App\Http\Requests\Users\Attendances;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user has permission to create attendances
        if (! $this->user()->can('create', Attendance::class)) {
            return false;
        }

        // If employee_id is provided, verify ownership
        if ($this->has('employee_id')) {
            $employee = Employee::find($this->input('employee_id'));
            if ($employee && $employee->user_id !== $this->user()->id) {
                return false;
            }
        }

        // If location_id is provided, verify ownership
        if ($this->has('location_id')) {
            $location = Location::find($this->input('location_id'));
            if ($location && $location->user_id !== $this->user()->id) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => 'required|integer|exists:employees,id',
            'location_id' => 'required|integer|exists:locations,id',
            'check_in' => 'required|date',
            'check_in_latitude' => 'nullable|numeric|between:-90,90',
            'check_in_longitude' => 'nullable|numeric|between:-180,180',
            'check_out' => 'nullable|date|after:check_in',
            'check_out_latitude' => 'nullable|numeric|between:-90,90',
            'check_out_longitude' => 'nullable|numeric|between:-180,180',
            'notes' => 'nullable|string|max:1000',
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
            'employee_id.required' => 'El empleado es obligatorio.',
            'employee_id.integer' => 'El ID del empleado debe ser un número entero.',
            'employee_id.exists' => 'El empleado seleccionado no existe.',
            'location_id.required' => 'La locación es obligatoria.',
            'location_id.integer' => 'El ID de la locación debe ser un número entero.',
            'location_id.exists' => 'La locación seleccionada no existe.',
            'check_in.required' => 'La hora de entrada es obligatoria.',
            'check_in.date' => 'La hora de entrada debe ser una fecha válida.',
            'check_in_latitude.numeric' => 'La latitud de entrada debe ser un número.',
            'check_in_latitude.between' => 'La latitud de entrada debe estar entre -90 y 90.',
            'check_in_longitude.numeric' => 'La longitud de entrada debe ser un número.',
            'check_in_longitude.between' => 'La longitud de entrada debe estar entre -180 y 180.',
            'check_out.date' => 'La hora de salida debe ser una fecha válida.',
            'check_out.after' => 'La hora de salida debe ser posterior a la hora de entrada.',
            'check_out_latitude.numeric' => 'La latitud de salida debe ser un número.',
            'check_out_latitude.between' => 'La latitud de salida debe estar entre -90 y 90.',
            'check_out_longitude.numeric' => 'La longitud de salida debe ser un número.',
            'check_out_longitude.between' => 'La longitud de salida debe estar entre -180 y 180.',
            'notes.string' => 'Las notas deben ser texto.',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres.',
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
            'check_in' => 'hora de entrada',
            'check_in_latitude' => 'latitud de entrada',
            'check_in_longitude' => 'longitud de entrada',
            'check_out' => 'hora de salida',
            'check_out_latitude' => 'latitud de salida',
            'check_out_longitude' => 'longitud de salida',
            'notes' => 'notas',
        ];
    }
}
