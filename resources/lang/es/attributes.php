<?php

return [
    // Common attributes
    'id' => 'ID',
    'name' => 'nombre',
    'email' => 'correo electrónico',
    'password' => 'contraseña',
    'password_confirmation' => 'confirmación de contraseña',
    'current_password' => 'contraseña actual',
    'created_at' => 'fecha de creación',
    'updated_at' => 'fecha de actualización',
    'deleted_at' => 'fecha de eliminación',

    // User attributes
    'user_id' => 'usuario',
    
    // Employee attributes
    'employee_id' => 'empleado',
    'employee_code' => 'código de empleado',
    'first_name' => 'nombre',
    'last_name' => 'apellido',
    'phone' => 'teléfono',
    'hire_date' => 'fecha de contratación',
    'position' => 'puesto',
    'is_active' => 'estado activo',
    'force_password_change' => 'forzar cambio de contraseña',

    // Location attributes
    'location_id' => 'locación',
    'address' => 'dirección',
    'city' => 'ciudad',
    'latitude' => 'latitud',
    'longitude' => 'longitud',
    'description' => 'descripción',

    // Attendance attributes
    'check_in' => 'hora de entrada',
    'check_out' => 'hora de salida',
    'notes' => 'notas',

    // Filter attributes
    'start_date' => 'fecha de inicio',
    'end_date' => 'fecha de fin',
    'status' => 'estado',
    'per_page' => 'por página',
    'page' => 'página',
    'sort_by' => 'ordenar por',
    'sort_direction' => 'dirección de orden',
    
    // Role and permission attributes
    'roles' => 'roles',
    'permissions' => 'permisos',
    'role_ids' => 'IDs de roles',
    'permission_ids' => 'IDs de permisos',
    
    // Additional attributes
    'username' => 'nombre de usuario',
    'per_page' => 'por página',
    'page' => 'página',
    'sort_by' => 'ordenar por',
    'sort_direction' => 'dirección de orden',
];