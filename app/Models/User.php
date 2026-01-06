<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name','email','password','role','status'];
    protected $hidden = ['password','remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isSupervisor(): bool { return $this->role === 'supervisor'; }
    public function isStudent(): bool { return $this->role === 'student'; }
    public function isEvaluator(): bool
    {
        return method_exists($this, 'evaluator') && $this->evaluator()->exists();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project:: class);
    }

    public function supervisedProjects(): HasMany
    {
        return $this->hasMany(Project:: class, 'supervisor_id');
    }

    public function supervisorProfile(): HasOne
    {
        return $this->hasOne(SupervisorProfile:: class);
    }

    // NEW: Evaluator profile (only for supervisors we mark as evaluators)
    public function evaluator(): HasOne
    {
        return $this->hasOne(Evaluator:: class);
    }

    /**
 * Check if student can create a new project
 * Rules:
 * - Maximum 3 projects allowed while in pending/rejected status
 * - Once any project is approved, no new submissions allowed
 */
public function canCreateNewProject(): bool
{
    // Only students can create projects
    if ($this->role !== 'student') {
        return false;
    }

    // Check if student has any approved project
    $hasApprovedProject = $this->projects()
        ->where('status', 'approved')
        ->exists();

    if ($hasApprovedProject) {
        return false;
    }

    // Check if student has reached the limit (3 projects)
    $projectCount = $this->projects()->count();
    
    return $projectCount < 3;
}

/**
 * Get reason why student cannot create new project
 */
public function getCannotCreateProjectReason(): ?string
{
    if ($this->role !== 'student') {
        return 'Only students can create projects. ';
    }

    $hasApprovedProject = $this->projects()
        ->where('status', 'approved')
        ->exists();

    if ($hasApprovedProject) {
        return 'You already have an approved project.';
    }

    $projectCount = $this->projects()->count();
    
    if ($projectCount >= 3) {
        return 'You have reached the maximum limit of 3 project submissions.';
    }

    return null;
}
}