<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'location_id',
        'check_in',
        'check_out',
        'latitude',
        'longitude',
        'notes',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function getTotalHoursAttribute(): ?float
    {
        if (! $this->check_out) {
            return null;
        }

        return $this->check_in->diffInHours($this->check_out, true);
    }

    public function scopeVisible($query, $user)
    {
        if ($user->hasRole('administrator')) {
            return $query;
        }

        if ($user->hasRole('manager')) {
            return $query->whereHas('employee', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
