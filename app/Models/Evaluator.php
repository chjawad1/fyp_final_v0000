<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluator extends Model
{
    protected $fillable = [
        'user_id',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function markAssigned(): void
    {
        $this->update(['status' => 'assigned']);
    }

    public function markAvailable(): void
    {
        $this->update(['status' => 'available']);
    }
}