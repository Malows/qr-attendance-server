<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'employee_code',
        'hire_date',
        'position',
        'notes',
        'is_active',
        'password',
        'force_password_change',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hire_date' => 'date',
        'password' => 'hashed',
        'force_password_change' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class)->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
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
     * Check if employee is assigned to a specific location
     */
    public function hasLocationAccess(Location $location): bool
    {
        return $this->locations()->where('location_id', $location->id)->exists();
    }

    /**
     * Get active locations assigned to this employee
     */
    public function getActiveLocationsAttribute()
    {
        return $this->locations()->where('is_active', true)->get();
    }

    /**
     * Assign employee to multiple locations
     */
    public function assignToLocations(array $locationIds): void
    {
        $this->locations()->syncWithoutDetaching($locationIds);
    }

    /**
     * Remove employee from multiple locations
     */
    public function removeFromLocations(array $locationIds): void
    {
        $this->locations()->detach($locationIds);
    }
}
