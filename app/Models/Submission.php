<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'status',
        'workflow_stage',
        'is_confirmed',
        'submitted_at',
        'reviewed_at',
        'validated_at',
        'published_at',
        'metadata'
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'validated_at' => 'datetime',
        'published_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contentItems()
    {
        return $this->hasMany(ContentItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function validations()
    {
        return $this->hasMany(Validation::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function workflows()
    {
        return $this->hasMany(Workflow::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByWorkflowStage($query, $stage)
    {
        return $query->where('workflow_stage', $stage);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('is_confirmed', true);
    }

    // Accessors & Mutators
    public function getStatusDisplayAttribute()
    {
        $statuses = [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'in_review' => 'In Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'in_validation' => 'In Validation',
            'validated' => 'Validated',
            'published' => 'Published',
            'completed' => 'Completed'
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}