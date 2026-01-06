<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionAssignment extends Model
{
    protected $fillable = [
        'defence_session_id',
        'user_id',
        'scores_json',
        'total_score',
        'remarks',
        'submitted_at',
    ];

    protected $casts = [
        'scores_json' => 'array',
        'submitted_at' => 'datetime',
    ];

    // Relationships
    public function session(): BelongsTo
    {
        return $this->belongsTo(DefenceSession::class, 'defence_session_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Auto-complete when all evaluations are submitted
    protected static function boot()
    {
        parent::boot();

        // When an evaluation is submitted, check if session should be completed
        static::updated(function ($assignment) {
            if ($assignment->wasChanged('submitted_at') && $assignment->submitted_at) {
                $assignment->checkSessionCompletion();
            }
        });

        static::created(function ($assignment) {
            if ($assignment->submitted_at) {
                $assignment->checkSessionCompletion();
            }
        });
    }

    /**
     * Check if all evaluations are submitted and auto-complete session
     */
    public function checkSessionCompletion()
    {
        $session = $this->session()->with('assignments')->first();
        
        if (!$session || $session->status !== 'scheduled') {
            return;
        }

        // Check if all assignments have been submitted
        $totalAssignments = $session->assignments()->count();
        $completedAssignments = $session->assignments()->whereNotNull('submitted_at')->count();

        // If all evaluations are submitted, mark session as completed
        if ($totalAssignments > 0 && $totalAssignments === $completedAssignments) {
            $session->update(['status' => 'completed']);
        }
    }
}