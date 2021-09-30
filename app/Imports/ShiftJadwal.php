<?php

namespace App\Imports;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\JadwalTemp;
use App\Models\Shift;
use App\Models\User;

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

        // delete data dengan bulan yang sama
        JadwalTemp::whereMonth('tanggal', $m)->whereYear('tanggal', $y)->delete();

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
                    $cek = JadwalTemp::where('user_id', $eos->uuid)->whereDate('tanggal', $tanggal)->first();

                    if($cek) {
                        $cek->update([
                            'kode_shift' => $val,
                            'tanggal' => $tanggal
                        ]);
                    } else {
                        $jadwal = JadwalTemp::create([
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

    }

    public function startRow(): int
    {
        return 3;
    }
    
}