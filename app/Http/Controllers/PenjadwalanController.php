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
use App\Models\JadwalTemp;
use App\Models\ShiftHistory;
use Carbon\Carbon;
use App\Imports\JadwalImport;
use App\Imports\ShiftJadwal;
use App\Models\Informasi;
use App\Models\InformasiUser;

class PenjadwalanController extends Controller
{
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->bulan = array(
            '1' => 'Januari',
            '2' => 'Februari',
            '3' => 'Maret',
            '4' => 'April',
            '5' => 'Mei',
            '6' => 'Juni',
            '7' => 'Juli',
            '8' => 'Agustus',
            '9' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        );
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
                $jadwal = Jadwal::with('user', 'shift', 'history', 'history.editoruser');
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

                if (!count($jadwal)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Not Found',
                        'code'    => 404,
                    ]);
                }

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
                
                return response()->json([
                    'success' => true,
                    'message' => 'OK',
                    'code'    => 200,
                    'data'  => $jadwal
                ]);
            }
        } catch (\Throwable $th) {
            return writeLog($th->getMessage());
        }
    }

    public function temp(Request $request) 
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
                $jadwal = JadwalTemp::with('user', 'shift', 'history');
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

                if (!count($jadwal)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Not Found',
                        'code'    => 404,
                    ]);
                }

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
                
                return response()->json([
                    'success' => true,
                    'message' => 'OK',
                    'code'    => 200,
                    'data'  => $jadwal
                ]);
                
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
                $jadwal = Jadwal::where('uuid', $id)->with('user', 'shift', 'history', 'history.editoruser')->first();

                if (!$jadwal) {
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

                    if (!$jadwal) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Not Found',
                            'code'    => 404,
                        ]);
                    }

                    // tambah history tukar shift
                    // $history = ShiftHistory::where('jadwal_shift_id', $jadwal->uuid)->first();
                    // if($history) {
                    //     $history->update([
                    //         'jadwal_shift_id' => $jadwal->uuid,
                    //         'tanggal_sebelumnya' => $jadwal->tanggal,
                            // 'shift_sebelumnya' => $jadwal->kode_shift,
                            // 'tanggal_sekarang'   => $this->request->tanggal,
                            // 'shift_sekarang'  => $this->request->kode,
                            // 'keterangan' => $this->request->keterangan 
                    //     ]);
                    // } else {
                        $history = ShiftHistory::create([
                            'uuid'     => generateUuid(),
                            'jadwal_shift_id' => $jadwal->uuid,
                            'tanggal_sebelumnya' => $jadwal->tanggal,
                            'shift_sebelumnya' => $jadwal->kode_shift,
                            'tanggal_sekarang'   => $this->request->tanggal,
                            'shift_sekarang'  => $this->request->kode,
                            'editor'  => $user->uuid,
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
                        'message' => 'Jadwal Menunggu Approval Dari Supervisor',
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
                    if (!$jadwal) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Not Found',
                            'code'    => 404,
                        ]);
                    }
                    
                    $jadwal->delete();
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'message' => 'Berhasil dihapus!',
                        'code'    => 200
                    ]);
                    
                } catch (\Throwable $th) {
                    DB::rollback();
                    // dd($th->getMessage());
                    return writeLog($th->getMessage());
                }
            }
    }

    public function history($id=null)
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
                $history = ShiftHistory::with('jadwal', 'editoruser', 'jadwal.user', 'jadwal.shift')->get();
                if (!count($history)) {
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
                    'data'  => $history
                ]);
                
            }
        } catch (\Throwable $th) {
            return writeLog($th->getMessage());
        }
    }

    public function dataTempTahun(Request $request)
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
            DB::beginTransaction();
            try
            {
                $jadwal = JadwalTemp::get()->groupBy(function($val) {
                    return Carbon::parse($val->tanggal)->format('Y');
                });

                if (!count($jadwal)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Not Found',
                        'code'    => 404,
                    ]);
                }

                $tahun = [];
                foreach($jadwal as $j => $val) {
                    array_push($tahun, $j);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Data Tahun',
                    'code'    => 200,
                    'data'    => $tahun
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                return writeLog($th->getMessage());
            }
        }
    }

    public function dataTempBulan(Request $request, $tahun)
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
            DB::beginTransaction();
            try
            {
                $jadwal = JadwalTemp::select('*');

                if ($request->tahun) {
                    $tahun = $request->tahun;
                    $jadwal = $jadwal->whereYear('tanggal', $request->tahun);
                }

                $jadwal = $jadwal->get()->groupBy(function($val) {
                    return Carbon::parse($val->tanggal)->format('m');
                });

                if (!count($jadwal)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Not Found',
                        'code'    => 404,
                    ]);
                }

                $bulan = [];
                foreach($jadwal as $j => $val) {
                    $data = [];
                    $data['bulan'] = $this->bulan[$j];
                    $data['value'] = $j;
                    array_push($bulan, $data);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Data Tahun',
                    'code'    => 200,
                    'data'    => $bulan
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                return writeLog($th->getMessage());
            }
        }
    }

    public function getListTemp(Request $request)
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
            DB::beginTransaction();
            try
            {
                $jadwal = JadwalTemp::get()->groupBy(function($val) {
                    return Carbon::parse($val->tanggal)->format('Y');
                });

                if (!count($jadwal)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Not Found',
                        'code'    => 404,
                    ]);
                }

                $data = [];
                foreach($jadwal as $j => $val) {
                    $bulan = JadwalTemp::select('*')->whereYear('tanggal', $j)->get()->groupBy(function($val) {
                        return Carbon::parse($val->tanggal)->format('m');
                    });

                    if (count($bulan)) {
                        foreach($bulan as $b => $valb) {
                            $input = [];
                            $input['tahun'] = $j;
                            $input['bulan'] = $this->bulan[(int)$b];
                            array_push($data, $input);
                        }
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Data Jadwal Belum Diapprove',
                    'code'    => 200,
                    'data'    => $data
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                return writeLog($th->getMessage());
            }
        }
    }

    public function approveTemp(Request $request)
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
            DB::beginTransaction();
            $validator = Validator::make($this->request->all(), [
                'month' => 'required',
                'year' => 'required',
            ]);
    
            if ($validator->fails()) {
                return writeLogValidation($validator->errors());
            }

            try
            {
                $month = $this->request->month;
                $year = $this->request->year;

                $jadwal = JadwalTemp::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->get();

                if (!count($jadwal)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Not Found',
                        'code'    => 404,
                    ]);
                }

                // input ke table jadwal asli
                foreach($jadwal as $item) {
                    $pindahjadwal = Jadwal::create([
                        'uuid'     => generateUuid(),
                        'user_id'   => $item->user_id,
                        'tanggal'   => date('Y-m-d', strtotime($item->tanggal)),
                        'kode_shift'    => $item->kode_shift,
                        'created_at'    => date('Y-m-d H:i:s')
                    ]);
                }

                // delete jadwal temp
                JadwalTemp::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->delete();

                
                // kirim notif ke semua eos
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

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Jadwal Berhasil Diapprove!',
                    'code'    => 200
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                return writeLog($th->getMessage());
            }
        }
    }
}