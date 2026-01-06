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
}