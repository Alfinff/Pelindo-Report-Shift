<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\Jadwal;
use App\Models\Shift;
use App\Models\User;

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
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}