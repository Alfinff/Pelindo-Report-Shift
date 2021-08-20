<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Jadwal;

class JadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pagi = Jadwal::create([
            'uuid'  => generateUuid(),
            'user_id' => 'c4288c65-47f1-4527-8475-54206e724e5f',
            'tanggal' => Carbon::now()->toDateString(),
            'kode_shift' => 'P'
        ]);

        $siang = Jadwal::create([
            'uuid'  => generateUuid(),
            'user_id' => '25b088ec-aa35-4b79-9b88-e71d9dd98f5a',
            'tanggal' => Carbon::now()->toDateString(),
            'kode_shift' => 'S'
        ]);

        $malam = Jadwal::create([
            'uuid'  => generateUuid(),
            'user_id' => 'ea460a77-4a71-48d3-be4b-f53edb8e3c1a',
            'tanggal' => Carbon::now()->toDateString(),
            'kode_shift' => 'M'
        ]);
    }
}
