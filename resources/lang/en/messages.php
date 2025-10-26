<?php

return [
    // Authentication messages
    'login' => [
        'success' => 'Login successful.',
        'invalid_credentials' => 'Invalid credentials.',
        'user_not_found' => 'User not found with the provided email.',
        'employee_not_found' => 'Employee not found with the provided credentials.',
        'password_change_required' => 'Password change required.',
        'inactive_employee' => 'Employee account is inactive.',
    ],
    
    'logout' => [
        'success' => 'Successfully logged out.',
    ],
    
    'password' => [
        'updated' => 'Password updated successfully.',
        'reset' => 'Password reset successfully.',
        'current_incorrect' => 'Current password is incorrect.',
    ],

    // Attendance messages
    'attendance' => [
        'checked_in' => 'Check-in successful.',
        'checked_out' => 'Check-out successful.',
        'already_checked_in' => 'You already have an open attendance record.',
        'no_open_attendance' => 'No open attendance record found.',
        'created' => 'Attendance record created successfully.',
        'updated' => 'Attendance record updated successfully.',
        'deleted' => 'Attendance record deleted successfully.',
        'not_found' => 'Attendance record not found.',
    ],

    // Employee messages
    'employee' => [
        'created' => 'Employee created successfully.',
        'updated' => 'Employee updated successfully.',
        'deleted' => 'Employee deleted successfully.',
        'restored' => 'Employee restored successfully.',
        'not_found' => 'Employee not found.',
        'password_reset' => 'Employee password reset successfully.',
    ],

    // Location messages
    'location' => [
        'created' => 'Location created successfully.',
        'updated' => 'Location updated successfully.',
        'deleted' => 'Location deleted successfully.',
        'restored' => 'Location restored successfully.',
        'not_found' => 'Location not found.',
    ],

    // Role and permission messages
    'roles' => [
        'assigned' => 'Roles assigned successfully.',
        'revoked' => 'Roles revoked successfully.',
        'synced' => 'Roles synchronized successfully.',
    ],
    
    'permissions' => [
        'assigned' => 'Permissions assigned successfully.',
        'revoked' => 'Permissions revoked successfully.',
    ],

    // General messages
    'success' => 'Operation completed successfully.',
    'error' => 'An error occurred while processing your request.',
    'unauthorized' => 'Unauthorized access.',
    'forbidden' => 'Access forbidden.',
    'not_found' => 'Resource not found.',
    'validation_failed' => 'Validation failed.',
    'server_error' => 'Internal server error.',
];