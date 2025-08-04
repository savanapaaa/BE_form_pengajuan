<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    protected $table = 'pengajuans';

    protected $fillable = [
        'no_comtab', 'pin_sandi', 'tema', 'judul', 'jenis_media',
        'media_pemerintah', 'media_massa', 'jenis_konten',
        'petugas_pelaksana', 'supervisor', 'durasi', 'jumlah_produksi',
        'bukti_mengetahui', 'is_confirmed', 'tanggal_konfirmasi'
    ];

    public $timestamps = true;

    public function contentItems()
    {
        return $this->hasMany(ContentItem::class);
    }
}
