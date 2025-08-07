<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Validation extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'validator_id',
        'status',
        'notes',
        'validated_at',
        'assigned_at',
        'assigned_by',
        'publish_date',
        'published_content'
    ];

    protected $casts = [
        'validated_at' => 'datetime',
        'assigned_at' => 'datetime',
        'publish_date' => 'datetime',
        'published_content' => 'array'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_id');
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
        return $query->whereNull('validated_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('validated_at');
    }
}