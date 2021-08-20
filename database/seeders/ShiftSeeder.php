<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pagi = Shift::create([
            'uuid'     => generateUuid(),
            'nama'     => 'Pagi',
            'kode'     => 'P',
            'mulai'    => '07:00:00',
            'selesai'  => '16:00:00',
        ]);
        
        $sore = Shift::create([
            'uuid'     => generateUuid(),
            'nama'     => 'Siang',
            'kode'     => 'S',
            'mulai'    => '15:00:00',
            'selesai'  => '00:00:00',
        ]);

        $malam = Shift::create([
            'uuid'     => generateUuid(),
            'nama'     => 'Malam',
            'kode'     => 'M',
            'mulai'    => '23:00:00',
            'selesai'  => '08:00:00',
        ]);
    }
}
