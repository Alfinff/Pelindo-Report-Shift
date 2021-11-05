<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Profile;
use App\Models\Shift;
use App\Models\Jadwal;

class ShiftController extends Controller
{
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index() 
    {
        try 
        {
            $shift = Shift::paginate(25);
            $shift = $shift->setPath(env('APP_URL', 'https://centro.pelindo.co.id/api/shift/').'/utils/shift');
            if (empty($shift)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not Found',
                    'code'    => 404,
                ]);
            }
            else {
                return response()->json([
                    'success' => true,
                    'message' => 'OK',
                    'code'    => 200,
                    'data'  => $shift
                ]);
            }
        } catch (\Throwable $th) {
            return writeLog($th->getMessage());
        }
    }

    public function export()
    {
        try 
        {
            $jadwal = Jadwal::with('user', 'shift', 'history');

            if($this->request->month) {
                $jadwal = $jadwal->whereMonth('tanggal', $this->request->month);
            } else {
                $jadwal = $jadwal->whereMonth('tanggal', date('m'));
            }

            $jadwal = $jadwal->whereYear('tanggal', date('Y'))->get();
            
            if (empty($jadwal)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not Found',
                    'code'    => 404,
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'OK',
                'code'    => 200,
                'data'  => $jadwal
            ]);
        } catch (\Throwable $th) {
            return writeLog($th->getMessage());
        }
    }
}