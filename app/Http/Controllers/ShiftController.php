<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Profile;
use App\Models\Shift;

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
            $shift = $shift->setPath('https://pelindo.primakom.co.id/api/shift/utils/shift');
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
}