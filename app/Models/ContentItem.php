<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'type',
        'title',
        'content',
        'file_path',
        'file_url',
        'original_filename',
        'mime_type',
        'file_size',
        'order_index',
        'is_published',
        'metadata',
        // Review fields
        'reviewed_by',
        'reviewed_at',
        'review_status',
        'review_notes',
        // Validation fields
        'validation_assigned_to',
        'validated_by',
        'validation_assigned_at',
        'validated_at',
        'validation_status',
        'validation_notes',
        'publish_date',
        'published_content',
        'workflow_stage'
    ];

    protected $casts = [
        'metadata' => 'array',
        'published_content' => 'array',
        'order_index' => 'integer',
        'file_size' => 'integer',
        'is_published' => 'boolean',
        'reviewed_at' => 'datetime',
        'validation_assigned_at' => 'datetime',
        'validated_at' => 'datetime',
        'publish_date' => 'date'
    ];

    // Relationships
    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function validationAssignee()
    {
        return $this->belongsTo(User::class, 'validation_assigned_to');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeForValidation($query)
    {
        return $query->where('workflow_stage', 'validation')
                     ->where('review_status', 'approved');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('validation_assigned_to', $userId);
    }

    // Accessors
    public function getFileSizeHumanAttribute()
    {
        if (!$this->file_size) return null;
        
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIsFileAttribute()
    {
        return in_array($this->type, ['image', 'video', 'audio', 'document', 'file']);
    }

    public function getIsTextAttribute()
    {
        return in_array($this->type, ['text', 'link']);
    }

    // Mutators
    public function setOriginalFilenameAttribute($value)
    {
        $this->attributes['original_filename'] = $value;
        
        // Auto-detect mime type if not set
        if ($value && !$this->mime_type) {
            $extension = pathinfo($value, PATHINFO_EXTENSION);
            $this->attributes['mime_type'] = $this->getMimeTypeFromExtension($extension);
        }
    }

    // Helper methods
    private function getMimeTypeFromExtension($extension)
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
        ];

        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }
}