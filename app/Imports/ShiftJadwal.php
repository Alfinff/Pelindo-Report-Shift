<?php

namespace App\Imports;

use Illuminate\Http\Request;
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

    protected $month;

    public function __construct($month)
    {
        $this->month = $month; 
    }

    public function collection(Collection $rows)
    {
        $m = date('m');
        if($this->month) {
            $m = $this->month;
        }

        foreach ($rows as $row)
        {
            $shift = [];
            // jumlah hari bulan ini
            $d=cal_days_in_month(CAL_GREGORIAN,date('m'),date('y'));
            if($this->month) {
                $d=cal_days_in_month(CAL_GREGORIAN,date($this->month),date('y'));
            }
            for($i = 2; $i <= ((int)$d + 1); $i++) {
                array_push($shift, $row[$i]);
            }

            $eos = User::where('email', $row[1])->first();
            if(isset($eos->uuid)) {   
                $i = 1;
                foreach($shift as $item => $val) {   
                    $jadwal = Jadwal::create([
                        'uuid'  => generateUuid(),
                        'user_id' => $eos->uuid,
                        'kode_shift' => $val,
                        'tanggal' => date('Y').'-'.$m.'-'.$i
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