<?php

namespace App\Http\Controllers;

use App\Http\Resources\PengajuanResource;
use App\Models\Pengajuan;
use App\Models\ContentItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengajuanController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/pengajuan",
     *     tags={"Pengajuan"},
     *     summary="Get all pengajuan",
     *     description="Get list of all pengajuan with content items",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Pengajuan")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $pengajuans = Pengajuan::with('contentItems')->get();
        return PengajuanResource::collection($pengajuans);
    }

    /**
     * @OA\Post(
     *     path="/api/pengajuan",
     *     tags={"Pengajuan"},
     *     summary="Create new pengajuan",
     *     description="Create a new pengajuan with content items",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"noComtab", "tema", "judul"},
     *             @OA\Property(property="noComtab", type="string", example="CT001"),
     *             @OA\Property(property="pin", type="string", example="1234"),
     *             @OA\Property(property="tema", type="string", example="Sample Tema"),
     *             @OA\Property(property="judul", type="string", example="Sample Judul"),
     *             @OA\Property(property="jenisMedia", type="string", example="Video"),
     *             @OA\Property(property="petugasPelaksana", type="string", example="John Doe"),
     *             @OA\Property(property="supervisor", type="string", example="Jane Doe"),
     *             @OA\Property(property="durasi", type="string", example="30 menit"),
     *             @OA\Property(property="jumlahProduksi", type="integer", example=1),
     *             @OA\Property(property="tanggalOrder", type="string", format="date", example="2025-08-06"),
     *             @OA\Property(property="tanggalSubmit", type="string", format="date", example="2025-08-06"),
     *             @OA\Property(property="isConfirmed", type="boolean", example=false),
     *             @OA\Property(property="workflowStage", type="string", example="draft"),
     *             @OA\Property(
     *                 property="contentItems",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="nama", type="string"),
     *                     @OA\Property(property="jenisKonten", type="string"),
     *                     @OA\Property(property="nomorSurat", type="string"),
     *                     @OA\Property(property="narasiText", type="string"),
     *                     @OA\Property(property="keterangan", type="string"),
     *                     @OA\Property(property="status", type="string", example="pending")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pengajuan created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Pengajuan")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Simpan data utama pengajuan
            $pengajuan = Pengajuan::create([
                'no_comtab' => $request->input('noComtab'),
                'pin_sandi' => $request->input('pin'),
                'tema' => $request->input('tema'),
                'judul' => $request->input('judul'),
                'jenis_media' => $request->input('jenisMedia'),
                'media_pemerintah' => json_encode($request->input('mediaPemerintah', [])),
                'media_massa' => json_encode($request->input('mediaMassa', [])),
                'jenis_konten' => json_encode($request->input('jenisKonten', [])),
                'petugas_pelaksana' => $request->input('petugasPelaksana'),
                'supervisor' => $request->input('supervisor'),
                'durasi' => $request->input('durasi'),
                'jumlah_produksi' => $request->input('jumlahProduksi'),
                'tanggal_order' => $request->input('tanggalOrder'),
                'tanggal_submit' => $request->input('tanggalSubmit'),
                'bukti_mengetahui' => $request->hasFile('uploadedBuktiMengetahui')
                    ? $request->file('uploadedBuktiMengetahui')->store('bukti-mengetahui')
                    : null,
                'is_confirmed' => $request->boolean('isConfirmed'),
                'workflow_stage' => $request->input('workflowStage'),
            ]);

            $items = $request->input('contentItems', []);

            foreach ($items as $index => $item) {
                $pengajuan->contentItems()->create([
                    'nama' => $item['nama'] ?? null,
                    'jenisKonten' => $item['jenisKonten'] ?? null,
                    'mediaPemerintah' => json_encode($item['mediaPemerintah'] ?? []),
                    'mediaMassa' => json_encode($item['mediaMassa'] ?? []),
                    'nomorSurat' => $item['nomorSurat'] ?? null,
                    'narasiText' => $item['narasiText'] ?? null,
                    'sourceNarasi' => json_encode($item['sourceNarasi'] ?? []),
                    'sourceAudioDubbing' => json_encode($item['sourceAudioDubbing'] ?? []),
                    'sourceAudioBacksound' => json_encode($item['sourceAudioBacksound'] ?? []),
                    'sourcePendukungLainnya' => json_encode($item['sourcePendukungLainnya'] ?? []),
                    'narasiFile' => json_encode($item['narasiFile'] ?? null),
                    'suratFile' => json_encode($item['suratFile'] ?? null),
                    'audioDubbingFile' => json_encode($item['audioDubbingFile'] ?? null),
                    'audioDubbingLainLainFile' => json_encode($item['audioDubbingLainLainFile'] ?? null),
                    'audioBacksoundFile' => json_encode($item['audioBacksoundFile'] ?? null),
                    'audioBacksoundLainLainFile' => json_encode($item['audioBacksoundLainLainFile'] ?? null),
                    'pendukungVideoFile' => json_encode($item['pendukungVideoFile'] ?? null),
                    'pendukungFotoFile' => json_encode($item['pendukungFotoFile'] ?? null),
                    'pendukungLainLainFile' => json_encode($item['pendukungLainLainFile'] ?? null),

                    'narasiFileId' => $item['narasiFileId'] ?? null,
                    'suratFileId' => $item['suratFileId'] ?? null,
                    'audioDubbingFileId' => $item['audioDubbingFileId'] ?? null,
                    'audioDubbingLainLainFileId' => $item['audioDubbingLainLainFileId'] ?? null,
                    'audioBacksoundFileId' => $item['audioBacksoundFileId'] ?? null,
                    'audioBacksoundLainLainFileId' => $item['audioBacksoundLainLainFileId'] ?? null,
                    'pendukungVideoFileId' => $item['pendukungVideoFileId'] ?? null,
                    'pendukungFotoFileId' => $item['pendukungFotoFileId'] ?? null,
                    'pendukungLainLainFileId' => $item['pendukungLainLainFileId'] ?? null,

                    'narasiFileManualText' => $item['narasiFileManualText'] ?? null,
                    'suratFileManualText' => $item['suratFileManualText'] ?? null,
                    'audioDubbingFileManualText' => $item['audioDubbingFileManualText'] ?? null,
                    'audioDubbingLainLainFileManualText' => $item['audioDubbingLainLainFileManualText'] ?? null,
                    'audioBacksoundFileManualText' => $item['audioBacksoundFileManualText'] ?? null,
                    'audioBacksoundLainLainFileManualText' => $item['audioBacksoundLainLainFileManualText'] ?? null,
                    'pendukungVideoFileManualText' => $item['pendukungVideoFileManualText'] ?? null,
                    'pendukungFotoFileManualText' => $item['pendukungFotoFileManualText'] ?? null,
                    'pendukungLainLainFileManualText' => $item['pendukungLainLainFileManualText'] ?? null,

                    'narasiSourceType' => json_encode($item['narasiSourceType'] ?? []),
                    'audioDubbingSourceType' => json_encode($item['audioDubbingSourceType'] ?? []),
                    'audioBacksoundSourceType' => json_encode($item['audioBacksoundSourceType'] ?? []),
                    'pendukungLainnyaSourceType' => json_encode($item['pendukungLainnyaSourceType'] ?? []),

                    'tanggalOrderMasuk' => isset($item['tanggalOrderMasuk']) ? Carbon::parse($item['tanggalOrderMasuk']) : null,
                    'tanggalJadi' => isset($item['tanggalJadi']) ? Carbon::parse($item['tanggalJadi']) : null,
                    'tanggalTayang' => isset($item['tanggalTayang']) ? Carbon::parse($item['tanggalTayang']) : null,
                    'keterangan' => $item['keterangan'] ?? null,
                    'status' => $item['status'] ?? 'pending',
                ]);
            }

            DB::commit();
            return response()->json($pengajuan->load('contentItems'), 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/pengajuan/{id}",
     *     tags={"Pengajuan"},
     *     summary="Get specific pengajuan",
     *     description="Get pengajuan by ID with content items",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Pengajuan")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pengajuan not found"
     *     )
     * )
     */
    public function show($id)
    {
        $pengajuan = Pengajuan::with('contentItems')->findOrFail($id);
        return new PengajuanResource($pengajuan);
    }

    /**
     * @OA\Put(
     *     path="/api/pengajuan/{id}",
     *     tags={"Pengajuan"},
     *     summary="Update pengajuan",
     *     description="Update an existing pengajuan",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="noComtab", type="string"),
     *             @OA\Property(property="pin", type="string"),
     *             @OA\Property(property="tema", type="string"),
     *             @OA\Property(property="judul", type="string"),
     *             @OA\Property(property="jenisMedia", type="string"),
     *             @OA\Property(property="petugasPelaksana", type="string"),
     *             @OA\Property(property="supervisor", type="string"),
     *             @OA\Property(property="durasi", type="string"),
     *             @OA\Property(property="jumlahProduksi", type="integer"),
     *             @OA\Property(property="isConfirmed", type="boolean"),
     *             @OA\Property(property="tanggalKonfirmasi", type="string", format="date"),
     *             @OA\Property(property="contentItems", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pengajuan updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Pengajuan")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pengajuan not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $pengajuan = Pengajuan::findOrFail($id);
            $pengajuan->update([
                'no_comtab' => $request->noComtab,
                'pin_sandi' => $request->pin,
                'tema' => $request->tema,
                'judul' => $request->judul,
                'jenis_media' => $request->jenisMedia,
                'media_pemerintah' => json_encode($request->mediaPemerintah),
                'media_massa' => json_encode($request->mediaMassa),
                'jenis_konten' => json_encode($request->jenisKonten),
                'petugas_pelaksana' => $request->petugasPelaksana,
                'supervisor' => $request->supervisor,
                'durasi' => $request->durasi,
                'jumlah_produksi' => $request->jumlahProduksi,
                'bukti_mengetahui' => json_encode($request->uploadedBuktiMengetahui),
                'is_confirmed' => $request->isConfirmed,
                'tanggal_konfirmasi' => $request->tanggalKonfirmasi,
            ]);

            // Hapus contentItems lama
            $pengajuan->contentItems()->delete();

            // Tambahkan contentItems baru
            foreach ($request->contentItems as $item) {
                $pengajuan->contentItems()->create([
                    'nama' => $item['nama'],
                    'jenisKonten' => $item['jenisKonten'],
                    'mediaPemerintah' => json_encode($item['mediaPemerintah']),
                    'mediaMassa' => json_encode($item['mediaMassa']),
                    'nomorSurat' => $item['nomorSurat'],
                    'narasiText' => $item['narasiText'],
                    'sourcePendukungLainnya' => json_encode($item['sourcePendukungLainnya']),
                    'pendukungLainLainFile' => json_encode($item['pendukungLainLainFile']),
                    'tanggalOrderMasuk' => $item['tanggalOrderMasuk'],
                    'tanggalJadi' => $item['tanggalJadi'],
                    'tanggalTayang' => $item['tanggalTayang'],
                    'keterangan' => $item['keterangan'],
                    'status' => $item['status'],
                    'tanggal_diproses' => $item['tanggalDiproses'],
                    'diproses_oleh' => $item['diprosesoleh'],
                ]);
            }

            DB::commit();
            return response()->json($pengajuan->load('contentItems'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/pengajuan/{id}",
     *     tags={"Pengajuan"},
     *     summary="Delete pengajuan",
     *     description="Delete a pengajuan by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pengajuan deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pengajuan not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $pengajuan->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
