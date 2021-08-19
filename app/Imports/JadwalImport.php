<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\Jadwal;
use App\Models\Shift;
use App\Models\User;
use App\Models\Informasi;
use App\Models\InformasiUser;

class JadwalImport implements ToCollection, WithStartRow
{
    /**
    * @param Collection $collection
    */

    public function collection(Collection $rows)
    {
        foreach ($rows as $row)
        {
            $eos = User::where('no_hp', $row[3])->first();
            $shift = Shift::where('nama', $row[1])->first();
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[0])->format('Y-m-d');

            $jadwal = Jadwal::create([
                'uuid'  => generateUuid(),
                'user_id' => $eos->uuid,
                'shift_id' => $shift->uuid,
                'tanggal' => $date
            ]);
        }
        
        // kirim Info Ke Pengguna
        $title = 'Jadwal Shift Baru';
        $isi = 'Ada jadwal shift baru yang telah dibuat';

        foreach(User::all() as $item) {
            $informasi = Informasi::create([
                'uuid'         => generateUuid(),
                'info_id'      => '-',
                'judul'        => $title,
                'isi'          => $isi,
                'jenis'        => env('NOTIF_SHIFT'),
            ]);

            InformasiUser::create([
                'uuid'         => generateUuid(),
                'user_id'      => $item->uuid,
                'informasi_id' => $informasi->uuid,
                'dibaca'       => 0,
            ]);

            if ($item->fcm_token) {
                $to      = $item->fcm_token;
                $payload = [
                    'title'    => $title,
                    'body'     => $isi,
                    'priority' => 'high',
                ];
                sendFcm($to, $payload, $payload);
            }
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}