<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/attachments/upload",
     *     tags={"Attachments"},
     *     summary="Upload file attachment",
     *     description="Upload a file and create attachment record",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(property="file", type="string", format="binary", description="File to upload (max 10MB)"),
     *                 @OA\Property(property="submission_id", type="integer", description="ID of related submission"),
     *                 @OA\Property(property="type", type="string", enum={"document", "image", "video", "audio", "other"}, description="File type category"),
     *                 @OA\Property(property="description", type="string", maxLength=255, description="File description")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Attachment"),
     *             @OA\Property(property="message", type="string", example="File uploaded successfully")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=413, description="File too large"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     * 
     * Upload a file attachment.
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|max:10240', // 10MB max
                'submission_id' => 'nullable|exists:submissions,id',
                'type' => 'nullable|string|in:document,image,video,audio,other',
                'description' => 'nullable|string|max:255'
            ]);
            
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();
            
            // Generate unique filename
            $filename = Str::uuid() . '.' . $extension;
            
            // Store file
            $path = $file->storeAs('attachments', $filename, 'private');
            
            // Determine file type if not provided
            $type = $validated['type'] ?? $this->determineFileType($mimeType);
            
            // Create attachment record
            $attachment = Attachment::create([
                'submission_id' => $validated['submission_id'] ?? null,
                'user_id' => $this->currentUserId(),
                'filename' => $filename,
                'original_name' => $originalName,
                'file_path' => $path,
                'file_size' => $size,
                'mime_type' => $mimeType,
                'type' => $type,
                'description' => $validated['description'] ?? null
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $attachment,
                'message' => 'File uploaded successfully'
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a file attachment.
     */
    public function download(string $id): mixed
    {
        try {
            $attachment = Attachment::findOrFail($id);
            
            // Check if user has permission to download this file
            if ($attachment->user_id !== $this->currentUserId() && !$this->currentUser()->hasRole(['admin', 'superadmin'])) {
                // Check if user has access to the submission this attachment belongs to
                if ($attachment->submission_id) {
                    $submission = $attachment->submission;
                    if ($submission->user_id !== $this->currentUserId() && 
                        $submission->assigned_to !== $this->currentUserId() && 
                        $submission->validation_assigned_to !== $this->currentUserId()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Unauthorized to download this file'
                        ], 403);
                    }
                }
            }
            
            if (!Storage::disk('private')->exists($attachment->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }
            
            return Storage::disk('private')->download(
                $attachment->file_path,
                $attachment->original_name
            );
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a file attachment.
     */
    public function delete(string $id): JsonResponse
    {
        try {
            $attachment = Attachment::findOrFail($id);
            
            // Check if user has permission to delete this file
            if ($attachment->user_id !== $this->currentUserId() && !$this->currentUser()->hasRole(['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this file'
                ], 403);
            }
            
            // Delete file from storage
            if (Storage::disk('private')->exists($attachment->file_path)) {
                Storage::disk('private')->delete($attachment->file_path);
            }
            
            // Delete attachment record
            $attachment->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attachment details.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $attachment = Attachment::with(['user', 'submission'])->findOrFail($id);
            
            // Check if user has permission to view this file
            if ($attachment->user_id !== $this->currentUserId() && !$this->currentUser()->hasRole(['admin', 'superadmin'])) {
                if ($attachment->submission_id) {
                    $submission = $attachment->submission;
                    if ($submission->user_id !== $this->currentUserId() && 
                        $submission->assigned_to !== $this->currentUserId() && 
                        $submission->validation_assigned_to !== $this->currentUserId()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Unauthorized to view this file'
                        ], 403);
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $attachment
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Attachment not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Determine file type based on MIME type.
     */
    private function determineFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        } elseif (in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv'
        ])) {
            return 'document';
        } else {
            return 'other';
        }
    }
}