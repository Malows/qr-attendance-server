<?php

namespace App\Http\Requests\Employees;

use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Don't authorize if there's already an open attendance
        // This will be handled by the controller with a proper error message
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
            'location_id' => 'required|integer|exists:locations,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
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
            'location_id.required' => __('validation.location_id.required'),
            'location_id.integer' => __('validation.location_id.integer'),
            'location_id.exists' => __('validation.location_id.exists'),
            'latitude.required' => __('validation.latitude.required'),
            'latitude.numeric' => __('validation.latitude.numeric'),
            'latitude.between' => __('validation.latitude.between'),
            'longitude.required' => __('validation.longitude.required'),
            'longitude.numeric' => __('validation.longitude.numeric'),
            'longitude.between' => __('validation.longitude.between'),
            'notes.string' => __('validation.notes.string'),
            'notes.max' => __('validation.notes.max'),
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
            'location_id' => __('attributes.location_id'),
            'latitude' => __('attributes.latitude'),
            'longitude' => __('attributes.longitude'),
            'notes' => __('attributes.notes'),
        ];
    }
}
