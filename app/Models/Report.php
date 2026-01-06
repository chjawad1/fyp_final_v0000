<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'title',
        'type',
        'parameters',
        'format',
        'status',
        'file_path',
        'generated_at',
        'generated_by',
    ];

    protected $casts = [
        'parameters' => 'array',
        'generated_at' => 'datetime',
    ];

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function getFileSizeAttribute(): ? string
    {
        if (! $this->file_path || !file_exists(storage_path('app/' . $this->file_path))) {
            return null;
        }
        
        $bytes = filesize(storage_path('app/' . $this->file_path));
        return $this->formatBytes($bytes);
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'generating' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}