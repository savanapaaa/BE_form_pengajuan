<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('content_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengajuan_id')->constrained()->onDelete('cascade');
            $table->string('nama');
            $table->string('jenisKonten');
            $table->json('mediaPemerintah')->nullable();
            $table->json('mediaMassa')->nullable();
            $table->string('nomorSurat')->nullable();
            $table->text('narasiText')->nullable();

            // Source types
            $table->json('narasiSourceType')->nullable();
            $table->json('audioDubbingSourceType')->nullable();
            $table->json('audioBacksoundSourceType')->nullable();
            $table->json('pendukungLainnyaSourceType')->nullable();

            // File JSON blob (optional)
            $table->json('narasiFile')->nullable();
            $table->json('suratFile')->nullable();
            $table->json('audioDubbingFile')->nullable();
            $table->json('audioDubbingLainLainFile')->nullable();
            $table->json('audioBacksoundFile')->nullable();
            $table->json('audioBacksoundLainLainFile')->nullable();
            $table->json('pendukungVideoFile')->nullable();
            $table->json('pendukungFotoFile')->nullable();
            $table->json('pendukungLainLainFile')->nullable();

            // File ID untuk referensi
            $table->string('narasiFileId')->nullable();
            $table->string('suratFileId')->nullable();
            $table->string('audioDubbingFileId')->nullable();
            $table->string('audioDubbingLainLainFileId')->nullable();
            $table->string('audioBacksoundFileId')->nullable();
            $table->string('audioBacksoundLainLainFileId')->nullable();
            $table->string('pendukungVideoFileId')->nullable();
            $table->string('pendukungFotoFileId')->nullable();
            $table->string('pendukungLainLainFileId')->nullable();

            // Manual Text
            $table->text('narasiFileManualText')->nullable();
            $table->text('suratFileManualText')->nullable();
            $table->text('audioDubbingFileManualText')->nullable();
            $table->text('audioDubbingLainLainFileManualText')->nullable();
            $table->text('audioBacksoundFileManualText')->nullable();
            $table->text('audioBacksoundLainLainFileManualText')->nullable();
            $table->text('pendukungVideoFileManualText')->nullable();
            $table->text('pendukungFotoFileManualText')->nullable();
            $table->text('pendukungLainLainFileManualText')->nullable();

            // Pendukung lainnya
            $table->json('sourceNarasi')->nullable();
            $table->json('sourceAudioDubbing')->nullable();
            $table->json('sourceAudioBacksound')->nullable();
            $table->json('sourcePendukungLainnya')->nullable();

            $table->dateTime('tanggalOrderMasuk')->nullable();
            $table->dateTime('tanggalJadi')->nullable();
            $table->dateTime('tanggalTayang')->nullable();

            $table->text('keterangan')->nullable();
            $table->string('status')->nullable();
            $table->string('tanggal_diproses')->nullable();
            $table->string('diproses_oleh')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_items');
    }
};
