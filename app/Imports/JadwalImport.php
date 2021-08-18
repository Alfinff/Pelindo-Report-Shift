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
            $date      = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[0])->format('Y-m-d');

            $jadwal = Jadwal::create([
                'uuid'  => generateUuid(),
                'user_id' => $eos->uuid,
                'shift_id' => $shift->uuid,
                'tanggal' => $date
            ]);

            // kirim Info Ke Pengguna
            $informasi = Informasi::create([
                'uuid'         => generateUuid(),
                'info_id'      => $jadwal->uuid,
                'judul'        => 'Jadwal Shift Baru',
                'isi'          => 'Pada tanggal '.$date.' terdapat jadwal shift anda',
                'jenis'        => env('NOTIF_SHIFT'),
            ]);

            InformasiUser::create([
                'uuid'         => generateUuid(),
                'user_id'      => $eos->uuid,
                'informasi_id' => $informasi->uuid,
                'dibaca'       => 0,
            ]);

            if ($eos->fcm_token) {
                $to      = $eos->fcm_token;
                $payload = [
                    'title'    => 'Jadwal Shift Baru',
                    'body'     => 'Pada tanggal '.$date.' terdapat jadwal shift anda',
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