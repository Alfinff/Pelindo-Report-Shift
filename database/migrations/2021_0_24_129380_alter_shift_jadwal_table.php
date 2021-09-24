<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterShiftJadwalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ms_shift_jadwal', function (Blueprint $table) {
            $table->string('approve', 1)->nullable();
            $table->datetime('approved_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ms_shift_jadwal', function (Blueprint $table) {
            $table->dropColumn('approve');
            $table->dropColumn('approved_at');
        });
    }
}
