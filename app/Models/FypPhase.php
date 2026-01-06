<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class FypPhase extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'semester',
        'order',
        'start_date',
        'end_date',
        'description',
        'allow_late',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'allow_late' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(PhaseSubmission::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySemester($query, string $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public function scopeCurrent($query)
    {
        return $query->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    /**
     * Helper Methods
     */
    public function isDeadlinePassed(): bool
    {
        return $this->end_date->endOfDay()->isPast();
    }

    public function isActive(): bool
    {
        return $this->is_active && 
               $this->start_date->startOfDay()->lte(now()) && 
               $this->end_date->endOfDay()->gte(now());
    }

    public function isUpcoming(): bool
    {
        return $this->start_date->startOfDay()->isFuture();
    }

    public function canSubmit(): bool
    {
        if ($this->isActive()) {
            return true;
        }

        if ($this->isDeadlinePassed() && $this->allow_late) {
            return true;
        }

        return false;
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->isDeadlinePassed()) {
            return 0;
        }

        return now()->diffInDays($this->end_date->endOfDay(), false);
    }

    public function getDaysOverdueAttribute(): int
    {
        if (! $this->isDeadlinePassed()) {
            return 0;
        }

        return $this->end_date->endOfDay()->diffInDays(now());
    }

    public function getStatusAttribute(): string
    {
        if ($this->isUpcoming()) {
            return 'upcoming';
        }

        if ($this->isActive()) {
            return 'active';
        }

        if ($this->isDeadlinePassed()) {
            return 'ended';
        }

        return 'inactive';
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'upcoming' => ['label' => 'Upcoming', 'color' => 'blue'],
            'active' => ['label' => 'Active', 'color' => 'green'],
            'ended' => ['label' => 'Ended', 'color' => 'gray'],
            default => ['label' => 'Inactive', 'color' => 'red'],
        };
    }

    /**
     * Get statistics for this phase
     */
    public function getStatsAttribute(): array
    {
        $submissions = $this->submissions();

        return [
            'total' => $submissions->count(),
            'not_started' => $submissions->where('status', 'not_started')->count(),
            'in_progress' => $submissions->where('status', 'in_progress')->count(),
            'submitted' => $submissions->where('status', 'submitted')->count(),
            'approved' => $submissions->where('status', 'approved')->count(),
            'rejected' => $submissions->where('status', 'rejected')->count(),
            'revision_required' => $submissions->where('status', 'revision_required')->count(),
            'late' => $submissions->where('is_late', true)->count(),
        ];
    }

    /**
     * Get all unique semesters
     */
    public static function getSemesters(): array
    {
        return self::distinct()->pluck('semester')->sort()->values()->toArray();
    }

    /**
     * Get phases for a specific semester
     */
    public static function getForSemester(string $semester)
    {
        return self::bySemester($semester)->ordered()->get();
    }

    /**
     * Get current active phase for a semester
     */
    public static function getCurrentPhase(string $semester)
    {
        return self::bySemester($semester)
                   ->active()
                   ->current()
                   ->first();
    }
}