<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhaseSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'fyp_phase_id',
        'status',
        'submitted_at',
        'approved_at',
        'remarks',
        'reviewed_by',
        'is_late',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_late' => 'boolean',
    ];

    /**
     * Status constants
     */
    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REVISION_REQUIRED = 'revision_required';

    /**
     * Relationships
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(FypPhase::class, 'fyp_phase_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    /**
     * Helper Methods
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function needsRevision(): bool
    {
        return $this->status === self::STATUS_REVISION_REQUIRED;
    }

    public function canResubmit(): bool
    {
        return in_array($this->status, [
            self::STATUS_REJECTED,
            self::STATUS_REVISION_REQUIRED,
        ]);
    }

    /**
     * Actions
     */
    public function markAsSubmitted(bool $isLate = false): void
    {
        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'is_late' => $isLate,
        ]);
    }

    public function approve(int $reviewerId, ? string $remarks = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'reviewed_by' => $reviewerId,
            'remarks' => $remarks,
        ]);
    }

    public function reject(int $reviewerId, ?string $remarks = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_by' => $reviewerId,
            'remarks' => $remarks,
        ]);
    }

    public function requestRevision(int $reviewerId, ?string $remarks = null): void
    {
        $this->update([
            'status' => self::STATUS_REVISION_REQUIRED,
            'reviewed_by' => $reviewerId,
            'remarks' => $remarks,
        ]);
    }

    /**
     * Get status badge info
     */
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            self::STATUS_NOT_STARTED => ['label' => 'Not Started', 'color' => 'gray'],
            self::STATUS_IN_PROGRESS => ['label' => 'In Progress', 'color' => 'blue'],
            self::STATUS_SUBMITTED => ['label' => 'Pending Review', 'color' => 'yellow'],
            self::STATUS_APPROVED => ['label' => 'Approved', 'color' => 'green'],
            self::STATUS_REJECTED => ['label' => 'Rejected', 'color' => 'red'],
            self::STATUS_REVISION_REQUIRED => ['label' => 'Revision Required', 'color' => 'orange'],
            default => ['label' => 'Unknown', 'color' => 'gray'],
        };
    }
}