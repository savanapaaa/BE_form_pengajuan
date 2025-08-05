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
