<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'reviewer_id',
        'status',
        'notes',
        'reviewed_at',
        'assigned_at',
        'assigned_by'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'assigned_at' => 'datetime'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereNull('reviewed_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('reviewed_at');
    }
}