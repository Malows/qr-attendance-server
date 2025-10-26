<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-attendances', 'api');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        $isAdmin = $user->hasRole('administrator', 'api');
        $isOwner = $user->id === $attendance->employee->user_id;

        return $user->hasPermissionTo('view-attendances', 'api') && ($isOwner || $isAdmin);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-attendances', 'api');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        $isAdmin = $user->hasRole('administrator', 'api');
        $isOwner = $user->id === $attendance->employee->user_id;

        return $user->hasPermissionTo('edit-attendances', 'api') && ($isOwner || $isAdmin);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        $isAdmin = $user->hasRole('administrator', 'api');
        $isOwner = $user->id === $attendance->employee->user_id;

        return $user->hasPermissionTo('delete-attendances', 'api') && ($isOwner || $isAdmin);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Attendance $attendance): bool
    {
        return $user->hasPermissionTo('delete-attendances', 'api') && $user->hasRole('administrator', 'api');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Attendance $attendance): bool
    {
        return $user->hasPermissionTo('delete-attendances', 'api') && $user->hasRole('administrator', 'api');
    }
}
