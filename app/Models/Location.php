<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'city',
        'latitude',
        'longitude',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class)->withTimestamps();
    }

    public function scopeVisible($query, $user)
    {
        if ($user->hasRole('administrator')) {
            return $query;
        }

        if ($user->hasRole('manager')) {
            return $query->where('user_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Check if location has a specific employee assigned
     */
    public function hasEmployee(Employee $employee): bool
    {
        return $this->employees()->where('employee_id', $employee->id)->exists();
    }

    /**
     * Get active employees assigned to this location
     */
    public function getActiveEmployeesAttribute()
    {
        return $this->employees()->where('is_active', true)->get();
    }

    /**
     * Assign multiple employees to this location
     */
    public function assignEmployees(array $employeeIds): void
    {
        $this->employees()->syncWithoutDetaching($employeeIds);
    }

    /**
     * Remove multiple employees from this location
     */
    public function removeEmployees(array $employeeIds): void
    {
        $this->employees()->detach($employeeIds);
    }
}
