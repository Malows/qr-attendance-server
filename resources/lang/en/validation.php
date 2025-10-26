<?php

return [
    // Common validation messages
    'required' => 'The :attribute field is required.',
    'integer' => 'The :attribute must be an integer.',
    'numeric' => 'The :attribute must be a number.',
    'string' => 'The :attribute must be a string.',
    'email' => 'The :attribute must be a valid email address.',
    'unique' => 'The :attribute has already been taken.',
    'exists' => 'The selected :attribute does not exist.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
    ],
    'max' => [
        'string' => 'The :attribute may not be greater than :max characters.',
    ],
    'min' => [
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'confirmed' => 'The :attribute confirmation does not match.',
    'date' => 'The :attribute is not a valid date.',
    'boolean' => 'The :attribute field must be true or false.',
    'in' => 'The selected :attribute is invalid.',

    // Custom validation messages for specific fields
    'location_id' => [
        'required' => 'The location is required.',
        'integer' => 'The location ID must be an integer.',
        'exists' => 'The selected location does not exist.',
    ],
    'latitude' => [
        'required' => 'The latitude is required.',
        'numeric' => 'The latitude must be a number.',
        'between' => 'The latitude must be between -90 and 90.',
    ],
    'longitude' => [
        'required' => 'The longitude is required.',
        'numeric' => 'The longitude must be a number.',
        'between' => 'The longitude must be between -180 and 180.',
    ],
    'notes' => [
        'string' => 'The notes must be text.',
        'max' => 'The notes may not exceed :max characters.',
    ],
    'password' => [
        'required' => 'The password is required.',
        'min' => 'The password must be at least :min characters.',
        'confirmed' => 'The password confirmation does not match.',
    ],
    'current_password' => [
        'required' => 'The current password is required.',
    ],
    'employee_code' => [
        'required' => 'The employee code is required.',
        'unique' => 'The employee code has already been taken.',
    ],
    'hire_date' => [
        'required' => 'The hire date is required.',
        'date' => 'The hire date is not a valid date.',
    ],
    'check_in' => [
        'required' => 'The check-in time is required.',
        'date' => 'The check-in time is not a valid date.',
    ],
    'check_out' => [
        'date' => 'The check-out time is not a valid date.',
        'after' => 'The check-out time must be after the check-in time.',
    ],
];