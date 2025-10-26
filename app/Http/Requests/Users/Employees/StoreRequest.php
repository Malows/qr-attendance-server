<?php

namespace App\Http\Requests\Users\Employees;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Employee::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'employee_code' => 'required|string|unique:employees,employee_code',
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
            'first_name.required' => __('validation.required'),
            'first_name.string' => __('validation.string'),
            'first_name.max' => __('validation.max.string'),
            'last_name.required' => __('validation.required'),
            'last_name.string' => __('validation.string'),
            'last_name.max' => __('validation.max.string'),
            'email.required' => __('validation.required'),
            'email.email' => __('validation.email'),
            'email.unique' => __('validation.unique'),
            'phone.string' => __('validation.string'),
            'phone.max' => __('validation.max.string'),
            'employee_code.required' => __('validation.employee_code.required'),
            'employee_code.string' => __('validation.string'),
            'employee_code.unique' => __('validation.employee_code.unique'),
            'password.string' => __('validation.string'),
            'password.min' => __('validation.min.string'),
            'hire_date.date' => __('validation.hire_date.date'),
            'position.string' => __('validation.string'),
            'position.max' => __('validation.max.string'),
            'notes.string' => __('validation.string'),
            'notes.max' => __('validation.max.string'),
            'is_active.boolean' => __('validation.boolean'),
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
            'first_name' => __('attributes.first_name'),
            'last_name' => __('attributes.last_name'),
            'email' => __('attributes.email'),
            'phone' => __('attributes.phone'),
            'employee_code' => __('attributes.employee_code'),
            'password' => __('attributes.password'),
            'hire_date' => __('attributes.hire_date'),
            'position' => __('attributes.position'),
            'notes' => __('attributes.notes'),
            'is_active' => __('attributes.is_active'),
        ];
    }
}
