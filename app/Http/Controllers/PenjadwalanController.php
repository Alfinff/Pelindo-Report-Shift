<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use App\Models\Jadwal;
use App\Models\ShiftHistory;
use Carbon\Carbon;
use App\Imports\JadwalImport;
use App\Imports\ShiftJadwal;

class PenjadwalanController extends Controller
{
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index(Request $request) 
    {
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
            else {
                $jadwal = Jadwal::with('user', 'shift', 'history');
                if ($request->nama) {
                    $nama = $request->nama;
                    $jadwal = $jadwal->whereHas('user', function ($q) use ($nama) {
                        $q->where('nama', 'ilike', '%'. $nama .'%');
                    });
                }
                if ($request->kode_shift) {
                    $shift = $request->kode_shift;
                    $jadwal = $jadwal->where('kode_shift', $shift);
                }

                $jadwal = $jadwal->get();
                $jadwal->map(function ($jadwal) {
                    if ($jadwal->shift != null) {
                       return $jadwal->shift->mulai = date('H:i', strtotime($jadwal->shift->mulai));
                    }
                });
                $jadwal->map(function ($jadwal) {
                    if ($jadwal->shift != null) {
                       return $jadwal->shift->selesai = date('H:i', strtotime($jadwal->shift->selesai));
                    }
                });
                // $jadwal = $jadwal->setPath('https://pelindo.primakom.co.id/api/shift/supervisor/jadwal');
                if (empty($jadwal)) {
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
                        'data'  => $jadwal
                    ]);
                }
            }
        } catch (\Throwable $th) {
            return writeLog($th->getMessage());
        }
    }

    public function show($id)
    {
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
            else {
                $jadwal = Jadwal::where('uuid', $id)->with('user', 'shift', 'history')->first();
                if (empty($jadwal)) {
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
                        'data'  => $jadwal
                    ]);
                }
            }
        } catch (\Throwable $th) {
            return writeLog($th->getMessage());
        }
    }

    public function update($id)
    {
        // return $this->request;
        $decodeToken = parseJwt($this->request->header('Authorization'));
            $uuid = $decodeToken->user->uuid;
            $user = User::where('uuid', $uuid)->first();
            
            // dd($this->request);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan',
                    'code'    => 404,
                ]);
            }
            else {
                DB::beginTransaction();
                try
                {
                    $jadwal = Jadwal::where('uuid', $id)->first();
                    if (empty($jadwal)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Not Found',
                            'code'    => 404,
                        ]);
                    }
                    else {

                        // tambah history tukar shift
                        // $history = ShiftHistory::where('jadwal_shift_id', $jadwal->uuid)->first();
                        // if($history) {
                        //     $history->update([
                        //         'jadwal_shift_id' => $jadwal->uuid,
                        //         'tanggal_sebelumnya' => $jadwal->tanggal,
                        //         'shift_sebelumnya' => $jadwal->kode_shift,
                        //         'keterangan' => $this->request->keterangan 
                        //     ]);
                        // } else {
                            $history = ShiftHistory::create([
                                'uuid'     => generateUuid(),
                                'jadwal_shift_id' => $jadwal->uuid,
                                'tanggal_sebelumnya' => $jadwal->tanggal,
                                'shift_sebelumnya' => $jadwal->kode_shift,
                                'keterangan' => $this->request->keterangan 
                            ]);
                        // }
                            
                        $jadwal->update([
                            'user_id'   => $this->request->user_id,
                            'kode_shift'  => $this->request->kode,
                            'tanggal'   => $this->request->tanggal
                        ]);

                        DB::commit();
                        return response()->json([
                            'success' => true,
                            'message' => 'Berhasil diubah!',
                            'code'    => 200
                        ]);
                    }
                } catch (\Throwable $th) {
                    DB::rollback();
                    return writeLog($th->getMessage());
                }
            }
    }

    public function store()
    {
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
            else 
            {
                $validator = Validator::make($this->request->all(), [
                    'file' => 'required|mimes:csv,xls,xlsx',
                    'month' => 'required',
                    'year' => 'required',
                ]);
        
                if ($validator->fails()) {
                    return writeLogValidation($validator->errors());
                }
                
                try{
                    $month = $this->request->month;
                    $year = $this->request->year;
                    
                    $current   = Carbon::now()->format('YmdHs');
                    $file = $this->request->file;
                    $nama_file = $current.'_'.$file->getClientOriginalName();
                    $file->move('jadwal',$nama_file);

                    Excel::import(new ShiftJadwal($month, $year), public_path('/jadwal/'.$nama_file));
                    unlink(public_path('/jadwal/'.$nama_file));
                    return response()->json([
                        'success' => true,
                        'message' => 'Created',
                        'code'    => 201
                    ]);
                } catch (\Throwable $th) {
                    return writeLog($th->getMessage());
                }
            }
        } catch (\Throwable $th) {
            return writeLog($th->getMessage());
        }
    }

    public function delete($id)
    {
        // return $this->request;
        $decodeToken = parseJwt($this->request->header('Authorization'));
            $uuid = $decodeToken->user->uuid;
            $user = User::where('uuid', $uuid)->first();
            
            // dd($this->request);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan',
                    'code'    => 404,
                ]);
            }
            else {
                DB::beginTransaction();
                try
                {
                    $jadwal = Jadwal::where('uuid', $id)->first();
                    if (empty($jadwal)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Not Found',
                            'code'    => 404,
                        ]);
                    }
                    else {
                        $jadwal->delete();
                        DB::commit();
                        return response()->json([
                            'success' => true,
                            'message' => 'Berhasil dihapus!',
                            'code'    => 200
                        ]);
                    }
                } catch (\Throwable $th) {
                    DB::rollback();
                    // dd($th->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => $th->getMessage(),
                        'code'    => 401
                    ]);
                }
            }
    }
}