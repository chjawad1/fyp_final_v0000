<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'supervisor_id',
        'title',
        'description',
        'status',
        'rejection_reason',
        'current_phase',
        'semester',
        'is_late',
    ];

    protected $casts = [
        'is_late' => 'boolean',
    ];

    /**
     * Phase constants
     */
    const PHASE_IDEA = 'idea';
    const PHASE_SCOPE = 'scope';
    const PHASE_DEFENCE = 'defence';
    const PHASE_COMPLETED = 'completed';

    /**
     * Existing Relationships
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function scopeDocuments(): HasMany
    {
        return $this->hasMany(ScopeDocument::class)->orderBy('created_at', 'desc');
    }

    public function latestScopeDocument(): HasOne
    {
        return $this->hasOne(ScopeDocument::class)->latestOfMany();
    }

    public function defenceSessions(): HasMany
    {
        return $this->hasMany(DefenceSession::class, 'project_id');
    }

    /**
     * New Phase Relationships
     */
    public function phaseSubmissions(): HasMany
    {
        return $this->hasMany(PhaseSubmission::class);
    }

    public function currentPhaseSubmission(): HasOne
    {
        return $this->hasOne(PhaseSubmission::class)
                    ->whereHas('phase', function ($query) {
                        $query->where('slug', $this->current_phase);
                    })
                    ->latestOfMany();
    }

    /**
     * Existing Scopes
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * New Phase Scopes
     */
    public function scopeInPhase($query, string $phase)
    {
        return $query->where('current_phase', $phase);
    }

    public function scopeBySemester($query, string $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeLateSubmissions($query)
    {
        return $query->where('is_late', true);
    }

    /**
     * Phase Helper Methods
     */
    public function isInPhase(string $phase): bool
    {
        return $this->current_phase === $phase;
    }

    public function isIdeaPhase(): bool
    {
        return $this->current_phase === self::PHASE_IDEA;
    }

    public function isScopePhase(): bool
    {
        return $this->current_phase === self::PHASE_SCOPE;
    }

    public function isDefencePhase(): bool
    {
        return $this->current_phase === self::PHASE_DEFENCE;
    }

    public function isCompleted(): bool
    {
        return $this->current_phase === self::PHASE_COMPLETED;
    }

    /**
     * Advance to next phase
     */
    public function advanceToNextPhase(): bool
    {
        $phases = [
            self::PHASE_IDEA => self::PHASE_SCOPE,
            self::PHASE_SCOPE => self::PHASE_DEFENCE,
            self::PHASE_DEFENCE => self::PHASE_COMPLETED,
        ];

        if (isset($phases[$this->current_phase])) {
            $this->update(['current_phase' => $phases[$this->current_phase]]);
            return true;
        }

        return false;
    }

    /**
     * Get current phase details from FypPhase table
     */
    public function getCurrentPhaseDetails()
    {
        if (!$this->semester) {
            return null;
        }

        $phaseSlugMap = [
            self::PHASE_IDEA => 'idea_approval',
            self::PHASE_SCOPE => 'scope_approval',
            self::PHASE_DEFENCE => 'defence',
        ];

        $slug = $phaseSlugMap[$this->current_phase] ?? null;

        if (!$slug) {
            return null;
        }

        return FypPhase::where('semester', $this->semester)
                       ->where('slug', $slug)
                       ->first();
    }

    /**
     * Check if current phase deadline has passed
     */
    public function isCurrentPhaseDeadlinePassed(): bool
    {
        $phase = $this->getCurrentPhaseDetails();

        if (!$phase) {
            return false;
        }

        return $phase->isDeadlinePassed();
    }

    /**
     * Check if can submit in current phase
     */
    public function canSubmitInCurrentPhase(): bool
    {
        $phase = $this->getCurrentPhaseDetails();

        if (!$phase) {
            return true; // No phase configured, allow submission
        }

        return $phase->canSubmit();
    }

    /**
     * Get days remaining for current phase
     */
    public function getCurrentPhaseDeadlineInfo(): array
    {
        $phase = $this->getCurrentPhaseDetails();

        if (!$phase) {
            return [
                'has_deadline' => false,
                'deadline' => null,
                'days_remaining' => null,
                'is_overdue' => false,
                'days_overdue' => 0,
            ];
        }

        return [
            'has_deadline' => true,
            'deadline' => $phase->end_date,
            'days_remaining' => $phase->days_remaining,
            'is_overdue' => $phase->isDeadlinePassed(),
            'days_overdue' => $phase->days_overdue,
            'allow_late' => $phase->allow_late,
        ];
    }

    /**
     * Get phase badge info
     */
    public function getPhaseBadgeAttribute(): array
    {
        return match($this->current_phase) {
            self::PHASE_IDEA => ['label' => 'Idea Approval', 'color' => 'blue'],
            self::PHASE_SCOPE => ['label' => 'Scope Approval', 'color' => 'yellow'],
            self::PHASE_DEFENCE => ['label' => 'Defence', 'color' => 'purple'],
            self::PHASE_COMPLETED => ['label' => 'Completed', 'color' => 'green'],
            default => ['label' => 'Unknown', 'color' => 'gray'],
        };
    }

    /**
     * Get latest approved scope document
     */
    public function getApprovedScopeDocument()
    {
        return $this->scopeDocuments()
                    ->where('status', 'approved')
                    ->first();
    }

    /**
     * Get pending scope document (if any)
     */
    public function getPendingScopeDocument()
    {
        return $this->scopeDocuments()
                    ->where('status', 'pending')
                    ->first();
    }

    /**
     * Check if project has approved scope document
     */
    public function hasApprovedScopeDocument(): bool
    {
        return $this->scopeDocuments()
                    ->where('status', 'approved')
                    ->exists();
    }

    /**
     * Check if project is ready for defence scheduling
     */
    public function isReadyForDefence(): bool
    {
        return $this->status === 'approved' && 
               $this->hasApprovedScopeDocument() &&
               $this->current_phase === self::PHASE_DEFENCE;
    }
}