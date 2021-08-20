<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftJadwalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_shift_jadwal', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 191)->unique();
            $table->string('user_id');
            $table->date('tanggal');
            $table->string('kode_shift', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_shift_jadwal');
    }
}
