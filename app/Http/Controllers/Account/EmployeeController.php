<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function DataPersonal(Request $request)
    {
        try {
            $data = DB::connection('CrystalDH')->table('V_EmpAll')->where('EmployeeId', $request->input('jdeno'))->first();

            if (!$data) {
                return response()->json([
                    'message' => 'Data Karyawan tidak ditemukan',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'data'  => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        };
    }
}
