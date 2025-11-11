<?php

namespace App\Http\Controllers\DHLU;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\LogLineUp;
use Carbon\Carbon;
use Jenssegers\Agent\Facades\Agent;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmailDHLU;
use App\Mail\SendEmailNotRegis;



class OperatorLineUpController extends Controller
{
    public function EmailNotification() {
        $ToEmail = [
            'andhika.widiyatmana@ptdh.co.id',
            'Iqbal.Yuditya@ptdh.co.id',
            'Jimmi.Heriyanto@ptdh.co.id',
            'teuku.nazwar@ptdh.co.id',
            'ali.impron@ptdh.co.id',
            'yulianto.prasetyo@ptdh.co.id',
            'sugianto@ptdh.co.id',
            'Internship.IT@ptdh.co.id'
        ];

        $cekLog = DB::connection('BCPDWHS')
                    ->table('LOGLINEUP AS A')
                    ->join('EMPLOYEE AS B', 'A.JDENO', '=', 'B.JDENUMBER')
                    ->select('A.*', 'B.NAME')
                    ->whereRaw("TRUNC(A.date_lineup) = TRUNC(SYSDATE)")
                    ->get();

        $result = DB::table('LOGLINEUP')
                    ->select(DB::raw('TRUNC(DATE_LINEUP) as date_lineup, COUNT(TRUNC(DATE_LINEUP)) as summary'))
                    ->whereBetween(DB::raw('TRUNC(DATE_LINEUP)'), [DB::raw("TO_DATE('2024-03-18', 'yyyy-mm-dd')"), DB::raw('TRUNC(sysdate)')])
                    ->groupBy(DB::raw('TRUNC(DATE_LINEUP)'))
                    ->orderBy(DB::raw('TRUNC(DATE_LINEUP)'), 'asc')
                    ->get();

        $NotRegis = DB::connection('BCPDWHS')
                        ->table('LOGLINEUP AS A')
                        ->join('EMPLOYEE AS B', 'A.JDENO', '=', 'B.JDENUMBER')
                        ->select('A.*', 'B.NAME')
                        ->whereRaw("TRUNC(A.date_lineup) = TRUNC(SYSDATE)")
                        ->where('A.status', '=', 'Not Registered')
                        ->get();

        $Count      = DB::connection('BCPDWHS')
                        ->table('LOGLINEUP AS A')
                        ->select(DB::raw('COUNT(*) AS total_data'))
                        ->whereRaw("TRUNC(A.date_lineup) = TRUNC(SYSDATE)")
                        ->where('A.status', '=', 'Not Registered')
                        ->first();

        Mail::to($ToEmail)->send(new SendEmailDHLU($cekLog, $result));
        Mail::to($ToEmail)->send(new SendEmailNotRegis($NotRegis, $Count));
    }

    public function CheckIn(Request $request) {
        $ipAddress = $request->ip();
        $device = Agent::device();
        $Platform = Agent::platform();
        $currentDate = Carbon::now()->tz('Asia/Singapore')->format('Y-m-d H:i:s');
        $dateInGMT8 = $currentDate;

        $now                = Carbon::now()->tz('Asia/Singapore');
        $startDayShift      = Carbon::createFromTimeString('04:00:00', 'Asia/Singapore');
        $endDayShift        = Carbon::createFromTimeString('05:30:00', 'Asia/Singapore');
        $startNightShift    = Carbon::createFromTimeString('16:00:00', 'Asia/Singapore');
        $endNightShift      = Carbon::createFromTimeString('17:30:00', 'Asia/Singapore');
        // return $startDayShift;

        // if ( $now->between($startDayShift, $endDayShift) || $now->between($startNightShift, $endNightShift) ) {
            // return "Bisa Absen";
            $cekKaryawan        = DB::connection('sqlsrv_sql_bcp')->table('tabWorkers as A')
                                ->leftJoin('tabEquipment as B', 'B.EquipmentID', '=', 'A.EquipmentID')
                                ->select('A.WorkerID', 'A.strPayNumber as JDENO', 'A.strName as Name', 'A.strPosition', 'A.EquipmentID',
                                        'B.strName as Unit')
                                ->where('A.strPayNumber', $request->input('jdeno'))
            ->first();

            // return $cekKaryawan;

            if($cekKaryawan) {
                //Input LogLineUp
                    $maxid                                  = LogLineUp::max('id');
                        $urut                                   = abs($maxid + 1);
                        $simpan                                 = new LogLineUp();
                        $simpan->id                             = $urut;
                        $simpan->date_lineup                    = $dateInGMT8;
                        $simpan->jdeno                          = $request->input('jdeno');
                        $simpan->status                         = 'In';
                        $simpan->created_at                     = $dateInGMT8;
                        $simpan->address                        = $ipAddress;
                        $simpan->device                         = $device ? "On Progress" : "On Progress";
                        $simpan->platform                       = $Platform ? "On Progress" : "On Progress";
                    $simpan->save();

                if( substr($cekKaryawan->Unit, 0, 2) == "DT" ) {

                        $result = DB::connection('sqlsrv_sql_bcp')->table('tabCrewLineUp as A')
                            ->select(
                                'A.WorkerID', 'A.WorkerPriority as Skala', 'C.strPayNumber as JDENO', 'C.strName as Name', 'C.strPosition', 'B.EquipmentID', 'B.strName as Unit',
                                'D.Code', 'D.strDescription', 'E.LocationID', 'G.strName as Location', 'svl.UnitExca'
                                )
                            ->leftJoin('tabEquipment as B', 'B.EquipmentID', '=', 'A.EquipmentID')
                            ->leftJoin('tabWorkers as C', 'C.WorkerID', '=', 'A.WorkerID')
                            ->leftJoin('tabReasons as D', function($join){
                                $join->on('B.StatusID', '=', 'D.StatusID')
                                ->on('B.ReasonID', '=', 'D.ReasonID');
                            })
                            ->leftJoin('tabEquipmentTracking as E', 'E.EquipmentID', '=', 'A.EquipmentID')
                            ->leftJoin('tabLocations as F', 'F.LocationID', '=', 'E.LocationID')
                            ->leftJoin('tabLocations as G', 'G.LocationID', '=', 'F.RegionID')
                            ->leftJoin(DB::raw('(SELECT Y.EquipmentID, Y.AssignmentShovelID, Z.strName as UnitExca FROM tabAssignments Y
                                        LEFT JOIN tabEquipment Z ON Z.EquipmentID = Y.AssignmentShovelID) svl'),
                                'svl.EquipmentID', '=', 'A.EquipmentID')
                            ->where('A.WorkerID', '=', $cekKaryawan->WorkerID)
                        ->first();

                        // return $result;

                        return response()->json([
                            'pesan' => "Oke",
                            'data'  => $result,
                            'Gol'   => 'DT',
                            // 'Skala' => $Skala,
                        ]);
                }else {

                    $result = DB::connection('sqlsrv_sql_bcp')->table('tabCrewLineUp as A')
                            ->select('A.WorkerID', 'A.WorkerPriority as Skala', 'TW.strPayNumber as JDENO', 'TW.strName as Name', 'TW.strPosition', 'A.EquipmentID',
                            'B.strName as Unit', 'C.Code', 'C.strDescription', 'B.ActivityID', 'D.LocationID', 'F.strName as Location')
                            ->leftJoin('tabWorkers as TW', 'TW.WorkerID', '=', 'A.WorkerID')
                            ->leftjoin('tabEquipment as B', 'B.EquipmentID', '=', 'A.EquipmentID')
                            ->leftJoin('tabReasons as C', function($join){
                                $join->on('B.StatusID', '=', 'C.StatusID')
                                ->on('B.ReasonID', '=', 'C.ReasonID');
                            })
                            ->leftJoin('tabEquipmentTracking as D', 'D.EquipmentID', '=', 'A.EquipmentID')
                            ->leftJoin('tabLocations as E', 'E.LocationID', '=', 'D.LocationID')
                            ->leftJoin('tabLocations as F', 'F.LocationID', '=', 'E.RegionID')
                            ->where('TW.strPayNumber', '=', $request->input('jdeno'))
                    ->first();

                    return response()->json([
                        'pesan' => "Oke",
                        'data'  => $result,
                        'Gol'   => 'Exca',
                    ]);
                }
            }else{

                $maxid                                  = LogLineUp::max('id');
                $urut                                   = abs($maxid + 1);
                $simpan                                 = new LogLineUp();
                $simpan->id                             = $urut;
                $simpan->date_lineup                    = $dateInGMT8;
                $simpan->jdeno                          = $request->input('jdeno');
                $simpan->status                         = 'Not Registered';
                $simpan->created_at                     = $dateInGMT8;
                $simpan->address                        = $ipAddress;
                $simpan->device                         = $device ? "On Progress" : "On Progress";
                $simpan->platform                       = $Platform ? "On Progress" : "On Progress";
                $simpan->save();

                return response()->json([
                    'pesan' => "Tidak Oke",
                ]);
            }
        // } else {
        //     return response()->json([
        //         'pesan' => "Tidak Bisa Login",
        //     ]);
        // }
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
            $date = Carbon::now()->format('Y-m-d');
            // dd($date);//AttendanceTime
            $result = DB::connection('sqlsrv_sql_bcp') ->select('select A.*, B.WorkerPriority from UFN_CREWLINEUP() A
                                      left join tabCrewLineUp B on A.WorkerID = B.WorkerID  where A.NIK = :nik and A.dtUpdatedAt >= :date',
                                      [ 'nik' => $jdeno,
                                        'date' => $date
                                    ]);

            if( !empty($result) ) {
                $data = [];

                foreach( $result as $rows => $items ) {
                    $data[] = [
                        'WorkerID'      => $items->WorkerID,
                        'JDENO'         => $items->NIK,
                        'Name'          => $items->Worker,
                        'Skala'         => "Main Operator",
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

                return response()->json([
                    'pesan' => 'Oke',
                    'data'  => $data,
                ]);
            } else {
                $data = []; //AttendanceTime
                $resultSpare = DB::connection('sqlsrv_sql_bcp')->select('select A.*, B.WorkerPriority from ufn_CrewLineupSpare() A
                                    left join tabCrewLineUp B on A.WorkerID = B.WorkerID  where A.NIK = :nik and A.dtUpdatedAt >= :date',
                                    [   'nik' => $jdeno,
                                        'date' => $date
                                    ]);

                foreach( $resultSpare as $rows => $items ) {
                    $data[] = [
                        'WorkerID'      => $items->WorkerID,
                        'JDENO'         => $items->NIK,
                        'Name'          => $items->Worker,
                        'Skala'         => "Spare Operator",
                        'Description'   => "Mohon Menunggu Informasi Selanjutnya, Terimakasih"
                    ];
                }

                return response()->json([
                    'pesan' => 'Oke',
                    'data'  => $data,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'pesan' => 'Error occurred while executing the stored procedure.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function GetFinalCrewLineUpSpare($eqpID) {
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


//Data Collector Get Operator Vs Unit
    public function GetOperatorColector($jdeno, $NoUnit) {
        $data = DB::connection('oracle_bcp')->table('PTDHCARD as A')
                ->select(
                    'A.ID',
                    'A.EMPLOYEE_JDENUMBER as JDENO',
                    'A.EMPLOYEENAME',
                    // 'A.DEPARTMENTNAME',
                    'A.EMPLOYEEPOSITION',
                    'B.VEHICLETYPE_TYPE',
                    DB::raw('TRIM(C.DESCRIPTION) as Deskripsi')
                )
                ->leftJoin('VEHICLEPERMIT as B', 'A.ID', '=', 'B.PTDHCARD_ID')
                ->leftJoin('VEHICLETYPETEMP as C', DB::raw('TRIM(B.VEHICLETYPE_TYPE)'), '=', DB::raw('TRIM(C.TYPE)'))
                ->where('A.STATUS', '=', 'ACTIVE')
                ->whereIn('A.EMPLOYEEPOSITION', ['Operator'])
                ->whereNotIn('B.STATUS', ['INACTIVE'])
                ->where('A.EMPLOYEE_JDENUMBER', '=', $jdeno)
                ->orderBy('B.ID', 'desc')
        ->get();

        if( count($data) > 0 ) {
            $paramType = [];
            foreach( $data as $items) {
                $paramType[] = $items->deskripsi;
            }

            $type = DB::connection('oracle_bcp')->table('VEHICLETYPETEMP as A')
                    ->whereIn(DB::raw("TRIM(A.DESCRIPTION)"), $paramType)
                    ->whereNotNull('A.MODEL_ELLPRD')
            ->get();

            $paramModel = [];
            foreach( $type as $items) {
                $paramModel[] = $items->model_ellprd;
            }

            $model = DB::connection('oracle_bcp')->table('MODEL_EQUIPMENT as A')
                    ->whereIn('A.MODEL_ELLIPSE', $paramModel)
                    ->where(DB::raw("TRIM(A.PLANT_NO)"), $NoUnit)
            ->first();

            if( $model ) {
                return response()->json([
                    'pesan' => true
                ]);
            } else {
                return response()->json([
                    'pesan' => false
                ]);
            }
        } else {
            return response()->json([
                'pesan' => false
            ]);
        }
    }

}
