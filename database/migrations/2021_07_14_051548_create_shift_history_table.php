<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_shift_history', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 191)->unique();
            $table->string('jadwal_shift_id');
            $table->string('tanggal_sebelumnya');
            $table->string('shift_sebelumnya');
            $table->text('keterangan')->nullable();
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
        Schema::dropIfExists('ms_shift_history');
    }
}
