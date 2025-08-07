<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'submission_id',
        'content_item_id',
        'uploaded_by',
        'original_name',
        'file_name',
        'file_path',
        'file_url',
        'disk',
        'mime_type',
        'file_extension',
        'file_size',
        'file_hash',
        'file_type',
        'width',
        'height',
        'duration',
        'thumbnail_path',
        'thumbnail_url',
        'has_preview',
        'status',
        'is_public',
        'is_virus_scanned',
        'is_safe',
        'upload_session_id',
        'upload_metadata',
        'upload_completed_at',
        'access_permissions',
        'download_count',
        'last_accessed_at',
        'processing_metadata',
        'processing_errors',
        'is_processed',
        'expires_at',
        'is_temporary',
        'description',
        'metadata',
        'alt_text',
        'caption'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration' => 'integer',
        'download_count' => 'integer',
        'has_preview' => 'boolean',
        'is_public' => 'boolean',
        'is_virus_scanned' => 'boolean',
        'is_safe' => 'boolean',
        'is_processed' => 'boolean',
        'is_temporary' => 'boolean',
        'upload_metadata' => 'array',
        'access_permissions' => 'array',
        'processing_metadata' => 'array',
        'metadata' => 'array',
        'upload_completed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    // Relationships
    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function contentItem()
    {
        return $this->belongsTo(ContentItem::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes
    public function scopeImages($query)
    {
        return $query->where('file_type', 'image');
    }

    public function scopeVideos($query)
    {
        return $query->where('file_type', 'video');
    }

    public function scopeDocuments($query)
    {
        return $query->where('file_type', 'document');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Accessors
    public function getFileSizeHumanAttribute()
    {
        return $this->formatBytes($this->file_size);
    }

    public function getIsImageAttribute()
    {
        return $this->file_type === 'image';
    }

    public function getIsVideoAttribute()
    {
        return $this->file_type === 'video';
    }

    public function getIsDocumentAttribute()
    {
        return $this->file_type === 'document';
    }

    public function getFullUrlAttribute()
    {
        if ($this->file_url) {
            return $this->file_url;
        }
        
        $disk = $this->disk ?: 'public';
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk($disk);
        return $storage->url($this->file_path);
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_url) {
            return $this->thumbnail_url;
        }
        
        if ($this->thumbnail_path) {
            $disk = $this->disk ?: 'public';
            /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
            $storage = Storage::disk($disk);
            return $storage->url($this->thumbnail_path);
        }
        
        return null;
    }

    // Helper Methods
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
        $this->update(['last_accessed_at' => now()]);
    }

    public function markAsProcessed($metadata = [])
    {
        $this->update([
            'status' => 'completed',
            'is_processed' => true,
            'processing_metadata' => $metadata,
            'upload_completed_at' => now()
        ]);
    }

    public function markAsFailed($error = null)
    {
        $this->update([
            'status' => 'failed',
            'processing_errors' => $error
        ]);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    // Boot method for automatic file type detection
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($attachment) {
            // Auto-detect file type from mime type
            if (!$attachment->file_type && $attachment->mime_type) {
                $attachment->file_type = $attachment->detectFileType($attachment->mime_type);
            }
            
            // Generate file hash
            if ($attachment->file_path && !$attachment->file_hash) {
                $attachment->file_hash = md5_file(storage_path('app/' . $attachment->file_path));
            }
        });
    }

    private function detectFileType($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) return 'image';
        if (str_starts_with($mimeType, 'video/')) return 'video';
        if (str_starts_with($mimeType, 'audio/')) return 'audio';
        if (in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ])) return 'document';
        
        return 'other';
    }
}