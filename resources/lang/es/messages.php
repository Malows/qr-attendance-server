<?php

return [
    // Authentication messages
    'login' => [
        'success' => 'Inicio de sesión exitoso.',
        'invalid_credentials' => 'Credenciales inválidas.',
        'user_not_found' => 'No se encontró un usuario con el correo proporcionado.',
        'employee_not_found' => 'No se encontró un empleado con las credenciales proporcionadas.',
        'password_change_required' => 'Se requiere cambio de contraseña.',
        'inactive_employee' => 'La cuenta del empleado está inactiva.',
    ],
    
    'logout' => [
        'success' => 'Sesión cerrada exitosamente.',
    ],
    
    'password' => [
        'updated' => 'Contraseña actualizada exitosamente.',
        'reset' => 'Contraseña restablecida exitosamente.',
        'current_incorrect' => 'La contraseña actual es incorrecta.',
    ],

    // Attendance messages
    'attendance' => [
        'checked_in' => 'Entrada registrada exitosamente.',
        'checked_out' => 'Salida registrada exitosamente.',
        'already_checked_in' => 'Ya tienes un registro de asistencia abierto.',
        'no_open_attendance' => 'No se encontró un registro de asistencia abierto.',
        'created' => 'Registro de asistencia creado exitosamente.',
        'updated' => 'Registro de asistencia actualizado exitosamente.',
        'deleted' => 'Registro de asistencia eliminado exitosamente.',
        'not_found' => 'Registro de asistencia no encontrado.',
    ],

    // Employee messages
    'employee' => [
        'created' => 'Empleado creado exitosamente.',
        'updated' => 'Empleado actualizado exitosamente.',
        'deleted' => 'Empleado eliminado exitosamente.',
        'restored' => 'Empleado restaurado exitosamente.',
        'not_found' => 'Empleado no encontrado.',
        'password_reset' => 'Contraseña del empleado restablecida exitosamente.',
    ],

    // Location messages
    'location' => [
        'created' => 'Ubicación creada exitosamente.',
        'updated' => 'Ubicación actualizada exitosamente.',
        'deleted' => 'Ubicación eliminada exitosamente.',
        'restored' => 'Ubicación restaurada exitosamente.',
        'not_found' => 'Ubicación no encontrada.',
    ],

    // Role and permission messages
    'roles' => [
        'assigned' => 'Roles asignados exitosamente.',
        'revoked' => 'Roles revocados exitosamente.',
        'synced' => 'Roles sincronizados exitosamente.',
    ],
    
    'permissions' => [
        'assigned' => 'Permisos asignados exitosamente.',
        'revoked' => 'Permisos revocados exitosamente.',
    ],

    // General messages
    'success' => 'Operación completada exitosamente.',
    'error' => 'Ocurrió un error al procesar su solicitud.',
    'unauthorized' => 'Acceso no autorizado.',
    'forbidden' => 'Acceso prohibido.',
    'not_found' => 'Recurso no encontrado.',
    'validation_failed' => 'La validación falló.',
    'server_error' => 'Error interno del servidor.',
];