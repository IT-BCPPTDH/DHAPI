<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    public function HistoryLeaveEmployee(Request $request)
    {
        $data = DB::connection('CrystalDH')->table('V_LvApp')->where('Empnik', $request->input('jdeno'))->get();
        return response()->json([
            'data'  => $data
        ], 200);
    }

    public function BalanceLeaveEmployee(Request $request)
    {
        $data = DB::connection('CrystalDH')->table('V_LVBalance')->where('Empnik', $request->input('jdeno'))->get();
        return response()->json([
            'data'  => $data
        ], 200);
    }
}
