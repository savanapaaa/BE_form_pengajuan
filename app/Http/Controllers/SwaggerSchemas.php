<?php

namespace App\Http\Controllers;

/**
 * @OA\Schema(
 *     schema="Pengajuan",
 *     type="object",
 *     title="Pengajuan",
 *     description="Pengajuan model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="no_comtab", type="string", example="CT001"),
 *     @OA\Property(property="pin_sandi", type="string", example="1234"),
 *     @OA\Property(property="tema", type="string", example="Sample Tema"),
 *     @OA\Property(property="judul", type="string", example="Sample Judul"),
 *     @OA\Property(property="jenis_media", type="string", example="Video"),
 *     @OA\Property(property="media_pemerintah", type="string", example="[]"),
 *     @OA\Property(property="media_massa", type="string", example="[]"),
 *     @OA\Property(property="workflow_stage", type="string", example="draft"),
 *     @OA\Property(property="petugas_pelaksana", type="string", example="John Doe"),
 *     @OA\Property(property="supervisor", type="string", example="Jane Doe"),
 *     @OA\Property(property="durasi", type="string", example="30 menit"),
 *     @OA\Property(property="jumlah_produksi", type="integer", example=1),
 *     @OA\Property(property="tanggal_order", type="string", format="date", example="2025-08-06"),
 *     @OA\Property(property="tanggal_submit", type="string", format="date", example="2025-08-06"),
 *     @OA\Property(property="tanggal_konfirmasi", type="string", format="date", nullable=true),
 *     @OA\Property(property="is_confirmed", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="contentItems",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ContentItem")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="Submission",
 *     type="object",
 *     title="Submission",
 *     description="Submission model for form submissions",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Sample Submission"),
 *     @OA\Property(property="description", type="string", example="This is a sample submission"),
 *     @OA\Property(property="status", type="string", enum={"draft", "submitted", "confirmed", "review", "validation", "completed", "rejected"}, example="draft"),
 *     @OA\Property(property="workflow_stage", type="string", enum={"form", "review", "validation", "completed"}, example="form"),
 *     @OA\Property(property="assigned_to", type="integer", nullable=true, example=null),
 *     @OA\Property(property="assigned_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="review_status", type="string", enum={"pending", "approved", "rejected"}, nullable=true),
 *     @OA\Property(property="review_notes", type="string", nullable=true),
 *     @OA\Property(property="reviewed_by", type="integer", nullable=true),
 *     @OA\Property(property="reviewed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="validation_status", type="string", enum={"pending", "validated", "published", "rejected"}, nullable=true),
 *     @OA\Property(property="validation_notes", type="string", nullable=true),
 *     @OA\Property(property="validated_by", type="integer", nullable=true),
 *     @OA\Property(property="validated_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="attachments", type="array", @OA\Items(ref="#/components/schemas/Attachment")),
 *     @OA\Property(property="contentItems", type="array", @OA\Items(ref="#/components/schemas/ContentItem"))
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="username", type="string", example="johndoe"),
 *     @OA\Property(property="role", type="string", enum={"admin", "user", "reviewer", "validator", "superadmin", "form", "review", "validasi", "rekap"}, example="user"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="Attachment",
 *     type="object",
 *     title="Attachment",
 *     description="File attachment model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="submission_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="content_item_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="uploaded_by", type="integer", example=1),
 *     @OA\Property(property="original_name", type="string", example="document.pdf"),
 *     @OA\Property(property="file_name", type="string", example="unique-filename.pdf"),
 *     @OA\Property(property="file_path", type="string", example="attachments/2025/08/unique-filename.pdf"),
 *     @OA\Property(property="file_url", type="string", nullable=true, example="https://example.com/storage/attachments/2025/08/unique-filename.pdf"),
 *     @OA\Property(property="disk", type="string", example="public"),
 *     @OA\Property(property="mime_type", type="string", example="application/pdf"),
 *     @OA\Property(property="file_extension", type="string", example="pdf"),
 *     @OA\Property(property="file_size", type="integer", example=1024000),
 *     @OA\Property(property="file_type", type="string", enum={"image", "video", "audio", "document", "other"}, example="document"),
 *     @OA\Property(property="status", type="string", enum={"uploading", "completed", "failed"}, example="completed"),
 *     @OA\Property(property="is_public", type="boolean", example=false),
 *     @OA\Property(property="download_count", type="integer", example=0),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="Review",
 *     type="object",
 *     title="Review",
 *     description="Review model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="submission_id", type="integer", example=1),
 *     @OA\Property(property="reviewer_id", type="integer", example=1),
 *     @OA\Property(property="status", type="string", enum={"approved", "rejected"}, example="approved"),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Review notes"),
 *     @OA\Property(property="reviewed_at", type="string", format="date-time"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="Validation",
 *     type="object",
 *     title="Validation",
 *     description="Validation model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="submission_id", type="integer", example=1),
 *     @OA\Property(property="validator_id", type="integer", example=1),
 *     @OA\Property(property="status", type="string", enum={"validated", "published", "rejected"}, example="validated"),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Validation notes"),
 *     @OA\Property(property="publish_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="published_content", type="object", nullable=true),
 *     @OA\Property(property="validated_at", type="string", format="date-time"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="ContentItem",
 *     type="object",
 *     title="Content Item",
 *     description="Content Item model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="pengajuan_id", type="integer", example=1),
 *     @OA\Property(property="nama", type="string", example="Content Name"),
 *     @OA\Property(property="jenis_konten", type="string", example="Video"),
 *     @OA\Property(property="nomor_surat", type="string", example="001/2025"),
 *     @OA\Property(property="narasi_text", type="string", example="Content description"),
 *     @OA\Property(property="keterangan", type="string", example="Additional notes"),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SwaggerSchemas extends Controller
{
    // This class is only for Swagger schema definitions
}
