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
        Schema::create('setting', function(Blueprint $table){
            $table->increments('id_setting');
            $table->string('nama_perusahaan');
            $table->text('alamat')->nullable();
            $table->string('telepon');
            $table->tinyInteger('tipe_nota');
            $table->string('path_logo');
            // $table->string('path_kartu_member');
            $table->timestamps();
            $table->string('instagram')->nullable(); // Pastikan kolom ini ada
            $table->string('facebook')->nullable();
            $table->string('twiter')->nullable();
            $table->string('youtube')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting');
    }
};
