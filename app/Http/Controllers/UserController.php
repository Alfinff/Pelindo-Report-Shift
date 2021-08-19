<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Profile;

class UserController extends Controller
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
            $user = Profile::where('user_id', $uuid)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan',
                    'code'    => 404,
                ]);
            }
            else {
                $user = User::paginate(25);
                if (empty($user)) {
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
                        'data'  => $user
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
            // dd($id);
            $decodeToken = parseJwt($this->request->header('Authorization'));
            $uuid = $decodeToken->user->uuid;
            $user = Profile::where('user_id', $uuid)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan',
                    'code'    => 404,
                ]);
            } else {
                $user = User::where('uuid', $id)->with('profile')->first();
                if (empty($user)) {
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
                        'data'  => $user
                    ]);
                }
            }   
        } catch (\Throwable $th) {
            return writeLog($th->getMessage());
        }
    }

    public function store() 
    {                
        try 
        {
            $decodeToken = parseJwt($this->request->header('Authorization'));
            $uuid = $decodeToken->user->uuid;
            $user = Profile::where('user_id', $uuid)->first();
            
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
                    'nama' => 'required',
                    'email' => 'required|email',
                    'password' => 'required',
                    'no_hp' => 'required',
                    'role' => 'required',
                    'jenis_kelamin' => 'required',
                    'alamat' => 'required',
                    // 'foto'   => 'required',
                ]);
        
                if ($validator->fails()) {
                    return writeLogValidation($validator->errors());
                }

                DB::beginTransaction();
                try{
                    $user = User::create([
                        'nama'  => $this->request->nama,
                        'email'   => $this->request->email,
                        'password' => Hash::make($this->request->password),
                        'no_hp'   => $this->request->no_hp,
                        'role'     => $this->request->role,
                        'uuid'     => generateUuid(),
                    ]);
                    $profil = Profile::create([
                        'alamat'  => $this->request->alamat,
                        'jenis_kelamin' => $this->request->jenis_kelamin,
                        'user_id' => $user->uuid,
                        'uuid'    => generateUuid(),
                    ]);
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'message' => 'Berhasil tambah user',
                        'code'    => 201
                    ]);
                }
                catch (\Throwable $th) {
                    DB::rollback();
                    dd($th->getMessage());
                }
                    
            }
        } catch (\Throwable $th) {
            // DB::rollback();
            dd($th->getMessage());
        }
    }

    public function update($id)
    {
        try 
        {
            $decodeToken = parseJwt($this->request->header('Authorization'));
            $uuid = $decodeToken->user->uuid;
            $user = Profile::where('user_id', $uuid)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan',
                    'code'    => 404,
                ]);
            }
            else {
                
                $validator = Validator::make($this->request->all(), [
                    'nama' => 'required',
                    'email' => 'required|email',
                    'no_hp' => 'required',
                    'role' => 'required',
                    'jenis_kelamin' => 'required',
                    'alamat' => 'required',
                    // 'foto'   => 'required',
                ]);
        
                if ($validator->fails()) {
                    return writeLogValidation($validator->errors());
                }

                DB::beginTransaction();
                try
                {
                    $user_updt = User::where('uuid', $id)->first();
                    // dd($user_updt);
                    if (empty($user_updt)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Not Found',
                            'code'    => 404,
                        ]);
                    }
                    else {
                        $user_updt->update([
                            'nama'  => $this->request->nama,
                            'email'   => $this->request->email,
                            'no_hp'   => $this->request->no_hp,
                            'role'     => $this->request->role,
                        ]);

                        $profile_updt = Profile::where('user_id', $id)->first();
                        $profile_updt->update([
                            'alamat'  => $this->request->alamat,
                            'jenis_kelamin' => $this->request->jenis_kelamin,
                        ]);
                    }
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'message' => 'OK',
                        'code'    => 200
                    ]);
                } catch (\Throwable $th) {
                    DB::rollback();
                    dd($th->getMessage());
                }
            }
        } catch (\Throwable $th) {
            return dd($th->getMessage());
        }
    }

    public function delete($id)
    {
        try 
        {
            $decodeToken = parseJwt($this->request->header('Authorization'));
            $uuid = $decodeToken->user->uuid;
            $user = Profile::where('user_id', $uuid)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan',
                    'code'    => 404,
                ]);
            }
            else 
            {
                $sel_user = User::where('uuid', $id)->first();
                $sel_profile = Profile::where('user_id', $id)->first();
                
                if (empty($sel_user)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Not Found',
                        'code'    => 404,
                    ]);
                }
                else {
                    $sel_user->delete();
                    $sel_profile->delete();

                    return response()->json([
                        'success' => true,
                        'message' => 'OK',
                        'code'    => 200
                    ]);
                }
            }
        } catch (\Throwable $th) {
            // DB::rollback();
            dd($th->getMessage());
        }
    }

}