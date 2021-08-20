<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\Jadwal;
use App\Models\Shift;
use App\Models\User;

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
            $shift = [$row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9], $row[10], $row[11], $row[12], $row[13], $row[14], $row[15], $row[16], $row[17], $row[18], $row[19], $row[20], $row[21], $row[22], $row[23], $row[24], $row[25], $row[26], $row[27], $row[28], $row[29], $row[30], $row[31], $row[32]];

            $eos = User::where('email', $row[1])->first();
            if(isset($eos->uuid)) {   
                $i = 1;
                foreach($shift as $item => $val) {   
                    $jadwal = Jadwal::create([
                        'uuid'  => generateUuid(),
                        'user_id' => $eos->uuid,
                        'kode_shift' => $val,
                        'tanggal' => '2021-08-'.$i
                    ]);
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