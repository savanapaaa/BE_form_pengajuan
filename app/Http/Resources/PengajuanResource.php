<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PengajuanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'noComtab' => $this->no_comtab,
            'pin' => $this->pin_sandi,
            'tema' => $this->tema,
            'judul' => $this->judul,
            'jenisMedia' => $this->jenis_media ?? 'digital',
            'mediaPemerintah' => $this->media_pemerintah ?? ['videotron', 'televisi'],
            'mediaMassa' => $this->media_massa ?? [],
            'jenisKonten' => $this->jenis_konten ?? ['infografis'],
            'petugasPelaksana' => $this->petugas_pelaksana,
            'supervisor' => $this->supervisor,
            'durasi' => $this->durasi ?? '30 hari',
            'jumlahProduksi' => $this->jumlah_produksi ?? '1',
            'uploadedBuktiMengetahui' => json_decode($this->bukti_mengetahui ?? 'null'),
            'isConfirmed' => $this->is_confirmed ?? true,
            'contentItems' => ContentItemResource::collection($this->whenLoaded('contentItems')),
            'tanggalKonfirmasi' => $this->tanggal_konfirmasi ?? '13/7/2025, 15.32.43'
        ];
    }
}
