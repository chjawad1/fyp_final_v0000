<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DefenceSession extends Model
{
    protected $fillable = [
        'committee_id',
        'project_id',
        'scheduled_at',
        'venue',
        'status',
        'scheduled_by_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    // Relationships
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(SessionAssignment::class);
    }

    // Query scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('scheduled_at', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('scheduled_at', '<', now());
    }

    /**
     * Get evaluation progress for admin tracking
     */
    public function getEvaluationProgressAttribute(): array
    {
        $total = $this->assignments()->count();
        $completed = $this->assignments()->whereNotNull('submitted_at')->count();
        
        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
            'is_complete' => $total > 0 && $total === $completed
        ];
    }

    /**
     * Check if all evaluations are submitted
     */
    public function hasAllEvaluationsSubmitted(): bool
    {
        $total = $this->assignments()->count();
        $completed = $this->assignments()->whereNotNull('submitted_at')->count();
        
        return $total > 0 && $total === $completed;
    }

    /**
     * Get detailed status with evaluation info
     */
    public function getDetailedStatusAttribute(): string
    {
        if ($this->status === 'scheduled') {
            $progress = $this->evaluation_progress;
            if ($progress['total'] > 0) {
                return "Scheduled ({$progress['completed']}/{$progress['total']} evaluations)";
            }
            return 'Scheduled (No evaluators assigned)';
        }
        
        return ucfirst($this->status);
    }

    // Existing methods
    public function getIsPastAttribute(): bool
    {
        return $this->scheduled_at < now();
    }

    public static function updatePastSessions(): int
    {
        return static::where('status', 'scheduled')
                     ->where('scheduled_at', '<', now())
                     ->update(['status' => 'completed']);
    }
}