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
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection(Collection $rows)
    {
        $m = date('m');
        $y = date('Y');
        if($this->month) {
            $m = date($this->month);
        }
        if($this->year) {
            $y = date($this->year);
        }

        foreach ($rows as $row)
        {
            $shift = [];
            // jumlah hari bulan ini
            $d=cal_days_in_month(CAL_GREGORIAN,$m,$y);
            // if($this->month && $this->year) {
            //     $d=cal_days_in_month(CAL_GREGORIAN,date($this->month),date($this->year));
            // }

            for($i = 2; $i <= ((int)$d + 1); $i++) {
                array_push($shift, $row[$i]);
            }

            $eos = User::where('email', $row[1])->first();
            if(isset($eos->uuid)) {   
                $i = 1;
                foreach($shift as $item => $val) {   
                    $tanggal = '';
                    $tanggal = $y.'-'.$m.'-'.$i;
                    $cek = Jadwal::where('user_id', $eos->uuid)->whereDate('tanggal', $tanggal)->first();

                    if($cek) {
                        $cek->update([
                            'kode_shift' => $val,
                            'tanggal' => $tanggal
                        ]);
                    } else {
                        $jadwal = Jadwal::create([
                            'uuid'  => generateUuid(),
                            'user_id' => $eos->uuid,
                            'kode_shift' => $val,
                            'tanggal' => $tanggal
                        ]);
                    }
                    
                    $i++;
                }
            }
        }

        $title = 'Jadwal Shift Baru';
        $isi = 'Ada jadwal shift baru yang telah dibuat';

        $informasi = Informasi::create([
            'uuid'         => generateUuid(),
            'info_id'      => '-',
            'judul'        => $title,
            'isi'          => $isi,
            'jenis'        => env('NOTIF_SHIFT'),
        ]);

        foreach(User::all() as $item) {
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