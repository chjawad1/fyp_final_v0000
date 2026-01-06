<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class DocumentTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'file_path',
        'created_by_id',
        'updated_by_id',
        'deleted_by_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (DocumentTemplate $m) {
            if (!$m->created_by_id) {
                $m->created_by_id = Auth::id();
            }
        });

        static::updating(function (DocumentTemplate $m) {
            $m->updated_by_id = Auth::id();
        });
    }

    // Relations to users (optional but useful in views)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by_id');
    }
}