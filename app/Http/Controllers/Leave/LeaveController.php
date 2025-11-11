<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    public function HistoryLeaveEmployee(Request $request)
    {
        $cekJDE = DB::connection('CrystalDH')->table('V_EmpAll')->where('EmployeeId', $request->input('jdeno'))->first();
        if ($cekJDE) {
            try {
                $data = DB::connection('CrystalDH')->table('V_LvApp')->where('Empnik', $request->input('jdeno'))->get();
                return response()->json([
                    'data'  => $data
                ], 200);
            } catch (\Exception $e) {
                return response()->json(["error" => $e->getMessage()], 500);
            };
        } else {
            return response()->json([
                'message' => "Data Karyawan tidak ditemukan",
            ], 200);
        }
    }

    public function BalanceLeaveEmployee(Request $request)
    {
        $cekJDE = DB::connection('CrystalDH')->table('V_EmpAll')->where('EmployeeId', $request->input('jdeno'))->first();
        if ($cekJDE) {
            try {
                $data = DB::connection('CrystalDH')->table('V_LVBalance')->where('Empnik', $request->input('jdeno'))->get();
                return response()->json([
                    'data'  => $data
                ], 200);
            } catch (\Exception $e) {
                return response()->json(["error" => $e->getMessage()], 500);
            };
        } else {
            return response()->json([
                'message' => "Data Karyawan tidak ditemukan",
            ], 200);
        }
    }
}
