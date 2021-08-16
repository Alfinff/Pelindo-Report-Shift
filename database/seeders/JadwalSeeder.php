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
            'shift_id' => '33c675ec-0206-497d-af3f-d61099898b51'
        ]);

        $siang = Jadwal::create([
            'uuid'  => generateUuid(),
            'user_id' => '25b088ec-aa35-4b79-9b88-e71d9dd98f5a',
            'tanggal' => Carbon::now()->toDateString(),
            'shift_id' => 'f4572358-1ac7-4d0b-b43c-9ef8f33db8f8'
        ]);

        $malam = Jadwal::create([
            'uuid'  => generateUuid(),
            'user_id' => 'ea460a77-4a71-48d3-be4b-f53edb8e3c1a',
            'tanggal' => Carbon::now()->toDateString(),
            'shift_id' => '70b5d31b-250e-48e1-a17b-96e649fc2771'
        ]);
    }
}
