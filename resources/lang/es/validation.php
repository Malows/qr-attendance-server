<?php

return [
    // Common validation messages
    'required' => 'El campo :attribute es obligatorio.',
    'integer' => 'El :attribute debe ser un número entero.',
    'numeric' => 'El :attribute debe ser un número.',
    'string' => 'El :attribute debe ser texto.',
    'email' => 'El :attribute debe ser una dirección de correo válida.',
    'unique' => 'El :attribute ya ha sido registrado.',
    'exists' => 'El :attribute seleccionado no existe.',
    'between' => [
        'numeric' => 'El :attribute debe estar entre :min y :max.',
    ],
    'max' => [
        'string' => 'El :attribute no puede tener más de :max caracteres.',
    ],
    'min' => [
        'string' => 'El :attribute debe tener al menos :min caracteres.',
    ],
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'date' => 'El :attribute no es una fecha válida.',
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'in' => 'El :attribute seleccionado es inválido.',

    // Custom validation messages for specific fields
    'location_id' => [
        'required' => 'La locación es obligatoria.',
        'integer' => 'El ID de la locación debe ser un número entero.',
        'exists' => 'La locación seleccionada no existe.',
    ],
    'latitude' => [
        'required' => 'La latitud es obligatoria.',
        'numeric' => 'La latitud debe ser un número.',
        'between' => 'La latitud debe estar entre -90 y 90.',
    ],
    'longitude' => [
        'required' => 'La longitud es obligatoria.',
        'numeric' => 'La longitud debe ser un número.',
        'between' => 'La longitud debe estar entre -180 y 180.',
    ],
    'notes' => [
        'string' => 'Las notas deben ser texto.',
        'max' => 'Las notas no pueden exceder :max caracteres.',
    ],
    'password' => [
        'required' => 'La contraseña es obligatoria.',
        'min' => 'La contraseña debe tener al menos :min caracteres.',
        'confirmed' => 'La confirmación de contraseña no coincide.',
    ],
    'current_password' => [
        'required' => 'La contraseña actual es obligatoria.',
    ],
    'employee_code' => [
        'required' => 'El código de empleado es obligatorio.',
        'unique' => 'El código de empleado ya ha sido registrado.',
    ],
    'hire_date' => [
        'required' => 'La fecha de contratación es obligatoria.',
        'date' => 'La fecha de contratación no es una fecha válida.',
    ],
    'check_in' => [
        'required' => 'La hora de entrada es obligatoria.',
        'date' => 'La hora de entrada no es una fecha válida.',
    ],
    'check_out' => [
        'date' => 'La hora de salida no es una fecha válida.',
        'after' => 'La hora de salida debe ser posterior a la hora de entrada.',
    ],
];