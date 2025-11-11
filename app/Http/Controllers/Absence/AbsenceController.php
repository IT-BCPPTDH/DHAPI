<?php

namespace App\Http\Controllers\Absence;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsenceController extends Controller
{
    public function HistoryAbsenceEmployee(Request $request)
    {
        $cekJDE = DB::connection('CrystalDH')->table('V_EmpAll')->where('EmployeeId', $request->input('jdeno'))->first();

        if ($cekJDE) {
            try {
                $validated = $request->validate([
                    'jdeno' => 'required|string',
                    'start' => 'required|date_format:Ymd',
                    'end'   => 'required|date_format:Ymd'
                ]);

                $empnik = $validated['jdeno'];
                $start = $validated['start'];
                $end = $validated['end'];

                $data = DB::connection('CrystalDH')
                    ->table("f_TMAbsence('$start', '$end')")
                    ->where('Empnik', $empnik)
                    ->get();
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
