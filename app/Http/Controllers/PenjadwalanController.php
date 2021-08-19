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
use Carbon\Carbon;
use App\Imports\JadwalImport;

class PenjadwalanController extends Controller
{
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index() 
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
                $jadwal = Jadwal::with('user', 'shift')->paginate(25);
                $jadwal = $jadwal->setPath('https://pelindo.primakom.co.id/api/shift/supervisor/jadwal');
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
            dd($th->getMessage());
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
                $jadwal = Jadwal::where('uuid', $id)->with('user', 'shift')->first();
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
            dd($th->getMessage());
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
                ]);
        
                if ($validator->fails()) {
                    return writeLogValidation($validator->errors());
                }
                
                try{
                    $current   = Carbon::now()->format('YmdHs');
                    $file = $this->request->file;
                    $nama_file = $current.'_'.$file->getClientOriginalName();
                    $file->move('jadwal',$nama_file);

                    Excel::import(new JadwalImport, public_path('/jadwal/'.$nama_file));
                    unlink(public_path('/jadwal/'.$nama_file));
                    return response()->json([
                        'success' => true,
                        'message' => 'Created',
                        'code'    => 201
                    ]);
                } catch (\Throwable $th) {
                    dd($th->getMessage());
                }
            }
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }
}