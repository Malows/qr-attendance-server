<?php

namespace App\Http\Requests\Employees;

use Illuminate\Foundation\Http\FormRequest;

class CheckOutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Employee can always attempt to check out
        // The controller will verify if there's an open attendance
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
            'latitude' => __('attributes.latitude'),
            'longitude' => __('attributes.longitude'),
            'notes' => __('attributes.notes'),
        ];
    }
}
