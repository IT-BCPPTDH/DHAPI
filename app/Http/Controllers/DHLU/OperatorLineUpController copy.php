<?php

namespace App\Http\Controllers\DHLU;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\LogLineUp;
use Carbon\Carbon;

class OperatorLineUpController extends Controller
{
    public function CheckIn(Request $request) {
        // Get the current date and time
        $currentDate = Carbon::now();
        // Set the timezone to GMT+8 (Asia/Singapore)
        $dateInGMT8 = $currentDate->tz('Asia/Singapore');

        $cekKaryawan        = DB::connection('sqlsrv_sql_bcp')->table('tabWorkers as A')
                            ->leftJoin('tabEquipment as B', 'B.EquipmentID', '=', 'A.EquipmentID')
                            ->select('A.WorkerID', 'A.strPayNumber as JDENO', 'A.strName as Name', 'A.strPosition', 'A.EquipmentID',
                                    'B.strName as Unit')
                            ->where('A.strPayNumber', $request->input('jdeno'))
        ->first();

        if($cekKaryawan) {
            // return substr($cekKaryawan->Unit, 0, 2);
            //Input LogLineUp
                $maxid                                  = LogLineUp::max('id');
                $urut                                   = abs($maxid + 1);
                $simpan                                 = new LogLineUp();
                $simpan->id                             = $urut;
                $simpan->date_lineup                    = $dateInGMT8;
                $simpan->jdeno                          = $request->input('jdeno');
                $simpan->status                         = 'In';
                $simpan->created_at                     = $dateInGMT8;
                $simpan->save();

            if( substr($cekKaryawan->Unit, 0, 2) == "DT" ) {
                $result = DB::connection('sqlsrv_sql_bcp')->table('tabWorkers as A')
                        ->select('A.WorkerID', 'A.strPayNumber as JDENO', 'A.strName as Name', 'A.strPosition', 'A.EquipmentID',
                                'B.strName as Unit', 'C.Code', 'C.strDescription', 'B.ActivityID', 'D.LocationID', 'F.strName as Location',
                                'svl.UnitExca')
                        ->leftjoin('tabEquipment as B', 'B.EquipmentID', '=', 'A.EquipmentID')
                        ->leftJoin('tabReasons as C', function($join){
                            $join->on('B.StatusID', '=', 'C.StatusID')
                            ->on('B.ReasonID', '=', 'C.ReasonID');
                        })
                        ->leftJoin('tabEquipmentTracking as D', 'D.EquipmentID', '=', 'A.EquipmentID')
                        ->leftJoin('tabLocations as E', 'E.LocationID', '=', 'D.LocationID')
                        ->leftJoin('tabLocations as F', 'F.LocationID', '=', 'E.RegionID')
                        ->leftJoin(DB::raw('(SELECT Y.EquipmentID, Y.AssignmentShovelID, Z.strName as UnitExca FROM tabAssignments Y
                                            LEFT JOIN tabEquipment Z ON Z.EquipmentID = Y.AssignmentShovelID) svl'),
                                    'svl.EquipmentID', '=', 'A.EquipmentID')
                        
                        ->where('A.strPayNumber', '=', $request->input('jdeno'))
                ->first();

                $Skala = DB::connection('sqlsrv_sql_bcp')->table('tabCrewLineUp as A')
                        ->select('WorkerPriority')
                        ->where('WorkerID', $result->WorkerID)
                ->first();

                    return response()->json([
                        'pesan' => "Oke",
                        'data'  => $result,
                        'Gol'   => 'DT',
                        'Skala' => $Skala->WorkerPriority,
                    ]);
            }else {
                $result = DB::connection('sqlsrv_sql_bcp')->table('tabWorkers as A')
                        ->select('A.WorkerID', 'A.strPayNumber as JDENO', 'A.strName as Name', 'A.strPosition', 'A.EquipmentID',
                                'B.strName as Unit', 'C.Code', 'C.strDescription', 'B.ActivityID', 'D.LocationID', 'F.strName as Location')
                        ->leftjoin('tabEquipment as B', 'B.EquipmentID', '=', 'A.EquipmentID')
                        ->leftJoin('tabReasons as C', function($join){
                            $join->on('B.StatusID', '=', 'C.StatusID')
                            ->on('B.ReasonID', '=', 'C.ReasonID');
                        })
                        ->leftJoin('tabEquipmentTracking as D', 'D.EquipmentID', '=', 'A.EquipmentID')
                        ->leftJoin('tabLocations as E', 'E.LocationID', '=', 'D.LocationID')
                        ->leftJoin('tabLocations as F', 'F.LocationID', '=', 'E.RegionID')
                        ->where('A.strPayNumber', '=', $request->input('jdeno'))
                ->first();

                $Skala = DB::connection('sqlsrv_sql_bcp')->table('tabCrewLineUp as A')
                        ->select('WorkerPriority')
                        ->where('WorkerID', $result->WorkerID)
                ->first();

                return response()->json([
                    'pesan' => "Oke",
                    'data'  => $result,
                    'Gol'   => 'Exca',
                    'Skala' => $Skala->WorkerPriority,
                ]);
            }
        }else{
            return response()->json([
                'pesan' => "Tidak Oke",
            ]);
        }
    }

    public function GetFinal(Request $request) {
        $crewLineup = DB::connection('sqlsrv_sql_bcp')->table('tabCrewLineUp as A')
        ->select(
            'A.WorkerID',
            'AA.strPayNumber as JDENO',
            'AA.strName as Name',
            'A.ShiftID as Shift',
            'A.WorkerPriority as Skala',
            'A.fIsLogin as FlsLogin',
            'A.EquipmentID as FidEqp',
            'B.strName as Unit',
            'A.EquipmentStatusID as EqpStatus',
            'C.strDescription as Status',
            'A.EquipmentReasonID as EqpReason',
            'A.EquipmentLocationID',
            'E.strName as Location'
        )
        ->leftJoin('tabWorkers as AA', 'AA.WorkerID', '=', 'A.WorkerID')
        ->leftJoin('tabEquipment as B', 'B.EquipmentID', '=', 'A.EquipmentID')
        ->leftJoin('tabReasons as C', function ($join) {
            $join->on('C.StatusID', '=', 'A.EquipmentStatusID')
                ->on('C.ReasonID', '=', 'A.EquipmentReasonID');
        })
        ->leftJoin('tabLocations as D', 'D.LocationID', '=', 'A.EquipmentLocationID')
        ->leftJoin('tabLocations as E', 'E.LocationID', '=', 'D.RegionID')
        ->where('A.AttendanceTime', '>=', date(now()))
        ->where('A.AttendanceTime', '<=', date(now()->addDays(1)))
        ->orderBy('A.WorkerPriority', 'asc')
        ->get();
        if (count($crewLineup) > 0){
            return response()->json([
                'pesan' => "Oke",
                'data'  => $crewLineup,
            ]);
        } else {
            return response()->json([
                'pesan' => "Tidak Ada Data",
                // 'data'  => $crewLineup,
            ]);
        }
    }

    public function GetFinalCrewLineUp($jdeno) {
        try {
            $result = DB::connection('sqlsrv_sql_bcp') ->select('select * from UFN_CREWLINEUP() where NIK = :nik', ['nik' => $jdeno]);
            // DB::select('select * from get_run_u(?, ?)', [$formattedDate, $shift]);
            $data = [];

            foreach( $result as $rows => $items ) {
                $data[] = [
                    'WorkerID'      => $items->WorkerID,
                    'JDENO'         => $items->NIK,
                    'Name'          => $items->Worker,
                    'Shift'         => $items->ShiftID,
                    'FidEqp'        => $items->EquipmentID,
                    'Unit'          => $items->AssignedEquipment,
                    'EqpStatus'     => $items->LineupStatus,
                    'Status'        => $items->EquipmentStatus,
                    'EqpReason'     => $items->EquipmentReason,
                    'EqpLocationID' => $items->EquipmentLocationID,
                    'Location'      => $items->EquipmentLocation,
                ];
            }

            if (!empty($result)) {
                return response()->json([
                    'pesan' => 'Oke',
                    'data'  => $data,
                ]);
            } else {
                return response()->json([
                    'pesan' => 'Tidak Ada Data',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'pesan' => 'Error occurred while executing the stored procedure.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function GetFinalCrewLineUpSpare($eqpID)
    {
        try {
            $results =  DB::connection('sqlsrv_sql_bcp')->table('tabCrewLineUp as clu')
                ->select(
                    'clu.dtUpdatedAt',
                    'clu.WorkerID',
                    'w.strName as Worker',
                    'w.strPayNumber as NIK',
                    'clu.WorkerPriority as Skala',
                    'clu.AttendanceTime',
                    'clu.AttendanceStatusID',
                    'clu.EquipmentID as FidEqp',
                    'ats.strName as AttendanceStatus',
                    // 'w.strEquipmentTypes',
                    // DB::raw('SUBSTRING((
                    //             SELECT \', \' + en.strName
                    //             FROM tabEnum en
                    //             CROSS APPLY (SELECT * FROM dbo.ufn_StringToTable(w.strEquipmentTypes, \', \', 0)) tp
                    //             WHERE en.EnumID = tp.Value
                    //             ORDER BY en.strName
                    //             FOR XML PATH(\'\')
                    //         ), 2, 200000) AS EquipmentTypes')
                )
                ->join('tabWorkers as w', 'w.WorkerID', '=', 'clu.WorkerID')
                ->leftJoin('tabEnum as ats', function ($join) {
                    $join->on('ats.EnumID', '=', 'clu.AttendanceStatusID')
                        ->where('ats.strType', '=', 'AttendanceStatus');
                })
                ->where('clu.fIsLogin', '=', 0)
                ->where('ats.strName', '=', 'Present')
                ->whereDate('clu.AttendanceTime', now()->toDateString())  // Filter by the current date
                ->where('clu.EquipmentID', $eqpID)
                ->orderBy('clu.AttendanceTime', 'asc')
                ->first();

            if ($results) {
                return response()->json([
                    'pesan' => 'Oke',
                    'data'  => $results,
                ]);
            } else {
                return response()->json([
                    'pesan' => 'Tidak Ada Data',
                ]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'pesan' => 'Error occurred while executing the stored procedure.',
            ]);
        }
    }

}
