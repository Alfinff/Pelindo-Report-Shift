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

class ShiftJadwal implements ToCollection, WithStartRow
{
    /**
    * @param Collection $collection
    */

    public function collection(Collection $rows)
    {
        foreach ($rows as $row)
        {
            $shift = [];
            // jumlah hari bulan ini
            $d=cal_days_in_month(CAL_GREGORIAN,date('m'),date('y'));
            for($i = 2; $i <= ((int)$d + 1); $i++) {
                array_push($shift, $row[$i]);
            }
            // $shift = [$row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9], $row[10], $row[11], $row[12], $row[13], $row[14], $row[15], $row[16], $row[17], $row[18], $row[19], $row[20], $row[21], $row[22], $row[23], $row[24], $row[25], $row[26], $row[27], $row[28], $row[29], $row[30], $row[31], $row[32]];

            $eos = User::where('email', $row[1])->first();
            if(isset($eos->uuid)) {   
                $i = 1;
                foreach($shift as $item => $val) {   
                    $jadwal = Jadwal::create([
                        'uuid'  => generateUuid(),
                        'user_id' => $eos->uuid,
                        'kode_shift' => $val,
                        'tanggal' => date('Y-m-').$i
                    ]);
                    $i++;
                }
            }
        }

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
        return 3;
    }
    
}