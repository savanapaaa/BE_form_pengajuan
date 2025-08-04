<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentItemResource extends JsonResource
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
            'nama' => $this->nama,
            'jenisKonten' => $this->jenisKonten,
            'mediaPemerintah' => json_decode($this->mediaPemerintah ?? '[]'),
            'mediaMassa' => json_decode($this->mediaMassa ?? '[]'),
            'nomorSurat' => $this->nomorSurat,
            'narasiText' => $this->narasiText,
            'sourceNarasi' => [], // kamu bisa isi dari relasi file jika ada
            'sourceAudioDubbing' => [],
            'sourceAudioBacksound' => [],
            'sourcePendukungLainnya' => ['lain-lain'],
            'narasiFile' => null,
            'suratFile' => null,
            'audioDubbingFile' => null,
            'audioDubbingLainLainFile' => null,
            'audioBacksoundFile' => null,
            'audioBacksoundLainLainFile' => null,
            'pendukungVideoFile' => null,
            'pendukungFotoFile' => null,
            'pendukungLainLainFile' => $this->pendukung_lain_file ?? null,
            'tanggalOrderMasuk' => $this->tanggalOrderMasuk,
            'tanggalJadi' => $this->tanggalJadi,
            'tanggalTayang' => $this->tanggalTayang,
            'keterangan' => $this->keterangan,
            'status' => $this->status ?? 'approved',
            'tanggalDiproses' => $this->tanggal_diproses ?? '13/7/2025, 15.32.38',
            'diprosesoleh' => $this->diproses_oleh ?? 'Admin',
        ];
    }
}
