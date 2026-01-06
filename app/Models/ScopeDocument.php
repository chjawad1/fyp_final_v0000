<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScopeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'version',
        'file_path',
        'changelog',
        'status',
        'feedback',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeNeedsRevision($query)
    {
        return $query->where('status', self::STATUS_REVISION_REQUIRED);
    }

    public function scopeReviewed($query)
    {
        return $query->whereNotNull('reviewed_at');
    }

    public function scopeUnreviewed($query)
    {
        return $query->whereNull('reviewed_at');
    }

    /**
     * Status Check Methods
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
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

    public function isReviewed(): bool
    {
        return $this->reviewed_at !== null;
    }

    /**
     * Action Methods
     */
    public function approve(int $reviewerId, ?string $feedback = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'feedback' => $feedback,
        ]);

        // Advance project to next phase if this is the latest document
        $project = $this->project;
        if ($project->isScopePhase() && $this->isLatestVersion()) {
            $project->advanceToNextPhase();
        }
    }

    public function reject(int $reviewerId, ?string $feedback = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'feedback' => $feedback,
        ]);
    }

    public function requestRevision(int $reviewerId, ?string $feedback = null): void
    {
        $this->update([
            'status' => self::STATUS_REVISION_REQUIRED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'feedback' => $feedback,
        ]);
    }

    /**
     * Check if this is the latest version for the project
     */
    public function isLatestVersion(): bool
    {
        $latestId = $this->project
                         ->scopeDocuments()
                         ->orderBy('created_at', 'desc')
                         ->value('id');

        return $this->id === $latestId;
    }

    /**
     * Get previous version (if exists)
     */
    public function getPreviousVersion()
    {
        return $this->project
                    ->scopeDocuments()
                    ->where('created_at', '<', $this->created_at)
                    ->orderBy('created_at', 'desc')
                    ->first();
    }

    /**
     * Get all previous versions
     */
    public function getAllPreviousVersions()
    {
        return $this->project
                    ->scopeDocuments()
                    ->where('id', '!=', $this->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Get status badge info for UI
     */
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            self::STATUS_PENDING => ['label' => 'Pending Review', 'color' => 'yellow'],
            self::STATUS_APPROVED => ['label' => 'Approved', 'color' => 'green'],
            self::STATUS_REJECTED => ['label' => 'Rejected', 'color' => 'red'],
            self::STATUS_REVISION_REQUIRED => ['label' => 'Revision Required', 'color' => 'orange'],
            default => ['label' => 'Unknown', 'color' => 'gray'],
        };
    }

    /**
     * Get version number based on order
     */
    public function getVersionNumberAttribute(): int
    {
        return $this->project
                    ->scopeDocuments()
                    ->where('created_at', '<=', $this->created_at)
                    ->count();
    }

    /**
     * Format version display
     */
    public function getVersionDisplayAttribute(): string
    {
        if ($this->version) {
            return $this->version;
        }

        return 'v' . $this->version_number;
    }
}