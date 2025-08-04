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
        Schema::table('pengajuans', function (Blueprint $table) {
            $table->string('jenis_media')->nullable();
            $table->json('media_pemerintah')->nullable();
            $table->json('media_massa')->nullable();
            $table->json('jenis_konten')->nullable();
            $table->string('durasi')->nullable();
            $table->string('jumlah_produksi')->nullable();
            $table->json('bukti_mengetahui')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->string('tanggal_konfirmasi')->nullable();
            $table->dateTime('tanggal_order')->nullable();
            $table->dateTime('tanggal_submit')->nullable();
            $table->string('workflow_stage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            //
        });
    }
};
