<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Profile;
use App\Models\Shift;
use App\Models\Jadwal;
use App\Models\User;

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

    public function addShift()
    {
        // cek validasi
        $validator = Validator::make($this->request->all(), [
            'userid' => 'required',
            'tanggal' => 'required',
            'kodeshift' => 'required',
        ]);

        if ($validator->fails()) {
            return writeLogValidation($validator->errors());
        }

        DB::beginTransaction();
        try 
        {
            $decodeToken = parseJwt($this->request->header('Authorization'));
            $uuid = $decodeToken->user->uuid;
            $user = User::where('uuid', $uuid)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan',
                    'code'    => 404,
                ]);
            }
            
            $userid = $this->request->userid;
            $tanggal = $this->request->tanggal;
            $kodeshift = $this->request->kodeshift;

            // cek user eos
            $cekuser = User::where('uuid', $userid)->first();
            if (!$cekuser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan',
                    'code'    => 404,
                ]);
            }

            // cek shift
            $cekshift = Shift::where('kode', $kodeshift)->first();
            if (!$cekshift) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift tidak ditemukan',
                    'code'    => 404,
                ]);
            }

            // cek apakah jadwal sudah ada
            $cekjadwal = Jadwal::where('user_id', $userid)->where('kode_shift', $kodeshift)->whereDate('tanggal', date('Y-m-d', strtotime($tanggal)))->first();
            if ($cekjadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal '.ucwords($cekuser->nama).' Pada Tanggal '.date('Y-m-d', strtotime($tanggal)).' Dengan Shift '.ucwords($cekshift->nama).' Sudah Ada',
                    'code'    => 404,
                ]);
            }

            // input jadwal baru
            $jadwal = Jadwal::create([
                'uuid'  => generateUuid(),
                'user_id' => $userid,
                'kode_shift' => $kodeshift,
                'tanggal' => date('Y-m-d', strtotime($tanggal))
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Berhasil Menambah Jadwal Baru',
                'code'    => 200
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return writeLog($th->getMessage());
        }
    }
}