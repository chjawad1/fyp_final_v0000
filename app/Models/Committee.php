<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Committee extends Model
{
    protected $fillable = ['name', 'description', 'created_by_id'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'committee_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(DefenceSession::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
    
}