<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentItem extends Model
{
    protected $table = 'content_items';

    protected $fillable = [
        'nama', 'jenisKonten', 'mediaPemerintah', 'mediaMassa', 'nomorSurat', 'narasiText',
        'sourceNarasi', 'sourceAudioDubbing', 'sourceAudioBacksound', 'sourcePendukungLainnya',
        'narasiFile', 'suratFile', 'audioDubbingFile', 'audioDubbingLainLainFile',
        'audioBacksoundFile', 'audioBacksoundLainLainFile', 'pendukungVideoFile', 'pendukungFotoFile',
        'pendukungLainLainFile', 'narasiFileId', 'suratFileId', 'audioDubbingFileId',
        'audioDubbingLainLainFileId', 'audioBacksoundFileId', 'audioBacksoundLainLainFileId',
        'pendukungVideoFileId', 'pendukungFotoFileId', 'pendukungLainLainFileId',
        'narasiFileManualText', 'suratFileManualText', 'audioDubbingFileManualText',
        'audioDubbingLainLainFileManualText', 'audioBacksoundFileManualText',
        'audioBacksoundLainLainFileManualText', 'pendukungVideoFileManualText',
        'pendukungFotoFileManualText', 'pendukungLainLainFileManualText',
        'narasiSourceType', 'audioDubbingSourceType', 'audioBacksoundSourceType',
        'pendukungLainnyaSourceType', 'tanggalOrderMasuk', 'tanggalJadi',
        'tanggalTayang', 'keterangan', 'status'
    ];


    public $timestamps = true;

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
