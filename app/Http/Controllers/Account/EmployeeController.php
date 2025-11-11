<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function DataPersonal(Request $request)
    {
        $data = DB::connection('CrystalDH')->table('V_EmpAll')->where('EmployeeId', $request->input('jdeno'))->first();
        return response()->json([
            'data'  => $data
        ], 200);
    }
}
