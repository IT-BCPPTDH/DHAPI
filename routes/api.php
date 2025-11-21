<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Adldap\Laravel\Facades\Adldap;
use Adldap\Exceptions\Auth\BindException;
use Adldap\Exceptions\AdldapException;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Http\Controllers\DHLU\OperatorLineUpController;
use App\Http\Controllers\PTDHCardController;
use App\Http\Controllers\Account\EmployeeController;
use App\Http\Controllers\Leave\LeaveController;
use App\Http\Controllers\Absence\AbsenceController;
use App\Http\Controllers\ActiveDirectoryController;

use App\Http\Controllers\PRETESTController;

use App\Models\V_WHS_MASTER;
use App\Models\V_ITEMASTER_SCM;
use App\Models\V_ISSUING_IR_SCM;
use App\Models\V_PO_RCV;
use App\Models\RCV_LOG_1;
use App\Models\RCV_LOG_2;
use App\Models\PROC_LOG_1;
use App\Models\ISSU_LOG_HDR;
use App\Models\ISSU_LOG_DTL;
use App\Models\ISSU_TRA_HDR;
use App\Models\ISSU_TRA_DTL;
use App\Models\H2_EMP_ALL;
use App\Models\V_PO_UT;
use App\Models\users;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get("/cekLogin", function (Request $request) {
    $username = $request->input("1");
    $password = $request->input("2");

    $ad = new \Adldap\Adldap();

    $config = [
        'hosts'    => [env("AD_HOST")],
        'base_dn'  => env("AD_BASE_DN"),
        'username' => env("AD_USERNAME"),
        'password' => env("AD_PASSWORD"),
    ];

    $ad->addProvider($config);

    try {
        $provider = $ad->connect();

        $us = 'jktptdh\\shrpoint';
        $pwd = 'Ptdh2010*';
        if ($provider->auth()->attempt($us, $pwd)) {
            $user = $provider->search()->where('employeeid', 'contains', $username)->get();

            $userData = json_decode($user, true); // Decode the JSON response

            if ($user->count() > 0) {
                $userData = $user->first()->getAttributes(); // Get the attributes of the first result

                $cn = $userData['cn'][0]; // Get the "cn" attribute
                $employeeID = $userData['employeeid'][0]; // Get the "employeeid" attribute
                $akun = $userData['samaccountname'][0];

                $akunjktpdh = 'jktptdh\\' . $akun;
                if ($provider->auth()->attempt($akunjktpdh, $password)) {
                    return response()->json(["data" => 'Sukses']);
                } else {
                    return response()->json(["data" => 'Wrong password']);
                }
                return response()->json(["data" => $employeeID]);
            } else {
                return response()->json(["data" => 'User not found']);
            }
        } else {
            $errorMessage = $provider->getConnection()->getLastError();
            if (strpos($errorMessage, 'Invalid credentials') !== false) {

                return response()->json(["data" => "Wrong password"]);
            } else {
                return response()->json(["data" => "User doesn't exist or LDAP binding error"]);
            }
        }
    } catch (\Adldap\Auth\BindException $e) {
        return response()->json(["data" => $e->message()]);
    }
});

Route::put('/resetpassword', function (Request $request) {
    $jdeNo = $request->input('1');
    //decode//
    preg_match('/\d+/', $jdeNo, $matches);
    $mainNumber = $matches[0];
    //dd(strval($mainNumber/12345)    );

    $user = users::where('jde_no', $mainNumber)->first();
    //encode
    //     $number = $jdeNo*12345;
    //     $randomString = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789');
    //     $combinedValue = $number . $randomString;
    // dd($combinedValue);

    if ($user) {
        $hashedPassword = '$2y$10$h8.LcAAhS3T7Lz6RNHjpke2JjrEo8rBEoExZuxqVI1Hav4LQ0AFhy';
        $user->update(['password' => $hashedPassword]);
        return response()->json(['message' => 'Password updated successfully']);
    } else {
        return response()->json(['message' => 'User not found'], 404);
    }
});

Route::get("/home", function () {
    echo "Welcome";
});

// MINELINK BCP
Route::get("/MinelinkBCP/getLogLineUp", function (Request $request) {
    $query = DB::connection('BCPDWHS')->table('loglineup')->select('id', 'date_lineup', 'jdeno', 'status')
        ->whereDate('DATE_LINEUP', '=', Carbon::today()->toDateString());
    $data = $query->get();
    if ($data->isEmpty()) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});

Route::post('/MinelinkBCP/GetOperatorLogin', [OperatorLineUpController::class, 'CheckIn']); //Existing Use On DHLU Apps

Route::get('/MinelinkBCP/GetOperatorLoginFinal', [OperatorLineUpController::class, 'GetFinal']);

Route::get('/MinelinkBCP/GetFinalCrewLineUp/{jdeno}', [OperatorLineUpController::class, 'GetFinalCrewLineUp']); //Existing Use On DHLU Apps

Route::get('/MinelinkBCP/GetFinalCrewLineUpSpare/{eqpID}', [OperatorLineUpController::class, 'GetFinalCrewLineUpSpare']);
Route::get('/MinelinkBCP/TestKirim', [OperatorLineUpController::class, 'EmailNotification']);
//


// API Data Colector
// Cek Operator by SIMPER
Route::get('/PTDHCARD', [PTDHCardController::class, 'GetCard']);

//Operator Vs Unit
Route::get('/GetDataOperator/{jdeno}/{NoUnit}', [OperatorLineUpController::class, 'GetOperatorColector']);
//


// API PO UT
Route::get("/getPOUT", function (Request $request) {
    $result = DB::connection('MSADMIN')->table('V_PO_UT')
        ->select('*')->get();
    // return $result;
    $formattedData = [];
    // $formattedData = $result->groupBy('dstrct_code', 'po_no', 'creation_date', 'expedite_code', 'due_site_date');
    // dd($formattedData);

    foreach ($result as $row) {
        $headerKey = "{$row->dstrct_code}_{$row->po_no}_{$row->creation_date}_{$row->due_site_date}_{$row->authsd_tot_amt}";

        if (!isset($formattedData[$headerKey])) {
            $formattedData[$headerKey] = [
                "CustCode" => 'PTDHUT',
                "District" => $row->dstrct_code,
                "Warehouse" => $row->whouse_id,
                "PONo" => $row->po_no,
                "PODate" => $row->creation_date,
                "Priority" => $row->expedite_code,
                "EDD" => $row->due_site_date,
                "ProjectNo" => $row->heading,
                // "Header" => $row->heading,
                // "total_po_amount" => $row->authsd_tot_amt,
                "items" => [],
            ];
        }

        $formattedData[$headerKey]['items'][] = [
            "POItemNo" => $row->po_item_no,
            "StockCode" => $row->preq_stk_code,
            "PN" => $row->part_no,
            "Description" => $row->description,
            "Qty" => $row->curr_qty_i,
            "Uom" => $row->unit_of_purch,
            "Price" => $row->gross_price_p,
        ];
    }
    $formattedArray = array_values($formattedData);

    return response()->json(["status" => "200", "data" => $formattedArray]);
});

// API PO TRAKINDO
Route::get("/getPOTrakindo", function (Request $request) {
    $result = DB::connection('MSADMIN')->table('V_PO_TRAKINDO')
        ->select('*')->get();
    // return $result;
    $formattedData = [];
    // $formattedData = $result->groupBy('dstrct_code', 'po_no', 'creation_date', 'expedite_code', 'due_site_date');
    // dd($formattedData);

    foreach ($result as $row) {
        $headerKey = "{$row->dstrct_code}_{$row->po_no}_{$row->creation_date}_{$row->due_site_date}_{$row->authsd_tot_amt}";

        if (!isset($formattedData[$headerKey])) {
            $formattedData[$headerKey] = [
                "CustCode" => 'PTDHTRAKINDO',
                "District" => $row->dstrct_code,
                "Warehouse" => $row->whouse_id,
                "PONo" => $row->po_no,
                "PODate" => $row->creation_date,
                "Priority" => $row->expedite_code,
                "EDD" => $row->due_site_date,
                "ProjectNo" => $row->heading,
                // "Header" => $row->heading,
                // "total_po_amount" => $row->authsd_tot_amt,
                "items" => [],
            ];
        }

        $formattedData[$headerKey]['items'][] = [
            "POItemNo" => $row->po_item_no,
            "StockCode" => $row->preq_stk_code,
            "PN" => $row->part_no,
            "Description" => $row->description,
            "Qty" => $row->curr_qty_i,
            "Uom" => $row->unit_of_purch,
            "Price" => $row->gross_price_p,
        ];
    }
    $formattedArray = array_values($formattedData);

    return response()->json(["status" => "200", "data" => $formattedArray]);
});


// DHJ38M - Header

Route::get("/getDHJ38M", function (Request $request) {
    try {
        $results = DB::connection('MSADMIN')->select("select * from V_DHJ38M");

        return response()->json([
            'status' => 'success',
            'data' => $results
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error fetching contract details: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});


// DHJ38M - Detail

Route::get("/getDHJ38MDetail", function (Request $request) {
    try {
        $results = DB::connection('MSADMIN')->select("select * from V_DHJ38M_DETAIL");

        return response()->json([
            'status' => 'success',
            'data' => $results
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error fetching contract details: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});









//uncatagorized//
Route::get("/getEMPdtlJDE", function (Request $request) {
    $jde = $request->input("1");

    $query = H2_EMP_ALL::query();
    if ($jde) {
        $query->where("JDE_NO", "LIKE", "%$jde%");
    }
    $data = $query->get();
    if ($data->isEmpty()) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});

Route::get("/listitmstrdstrct", function (Request $request) {
    $stockCode = $request->input("stock_code");
    $dstrctCode = $request->input("dstrct_code");

    $query = V_ITEMASTER_SCM::query();
    if ($stockCode) {
        $query->where("STOCK_CODE", "LIKE", "%$stockCode%");
    }
    if ($dstrctCode) {
        $query->where("DSTRCT_CODE", "LIKE", "%$dstrctCode%");
    }
    $data = $query->get();
    if ($data->isEmpty()) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});

Route::get("/listitmstrdstrctwhs", function (Request $request) {
    $stockCode = $request->input("stock_code");
    $dstrctCode = $request->input("dstrct_code");
    $whscode = $request->input("whs");

    $query = V_ITEMASTER_SCM::query();
    if ($stockCode) {
        $query->where("STOCK_CODE", "LIKE", "%$stockCode%");
    }
    if ($dstrctCode) {
        $query->where("DSTRCT_CODE", "LIKE", "%$dstrctCode%");
    }
    if ($dstrctCode) {
        $query->where("WHOUSE_ID", "LIKE", "%$whscode%");
    }
    $data = $query->get();
    if ($data->isEmpty()) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});

Route::get("/listitmstrdstrctwhsbin", function (Request $request) {
    $stockCode = $request->input("stock_code");
    $dstrctCode = $request->input("dstrct_code");
    $whscode = $request->input("whs");
    $bin = $request->input("bin");

    $query = V_ITEMASTER_SCM::query();
    if ($stockCode) {
        $query->where("STOCK_CODE", "LIKE", "%$stockCode%");
    }
    if ($dstrctCode) {
        $query->where("DSTRCT_CODE", "LIKE", "%$dstrctCode%");
    }
    if ($dstrctCode) {
        $query->where("WHOUSE_ID", "LIKE", "%$whscode%");
    }
    if ($bin) {
        $query->where("BIN_CODE", "LIKE", "%$bin%");
    }
    $data = $query->get();

    if ($data->isEmpty()) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});

Route::get("/listitmstrdstrctbin", function (Request $request) {
    $stockCode = $request->input("1");
    $dstrctCode = $request->input("2");
    $bin = $request->input("3");

    $query = V_ITEMASTER_SCM::query();
    if ($stockCode) {
        $query->where("STOCK_CODE", "LIKE", "%$stockCode%");
    }
    if ($dstrctCode) {
        $query->where("DSTRCT_CODE", "LIKE", "%$dstrctCode%");
    }
    if ($bin) {
        $query->where("BIN_CODE", "LIKE", "%$bin%");
    }
    $data = $query->get();

    if ($data->isEmpty()) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});
Route::get("/listwhs", function (Request $request) {
    $search = $request->input("dstrct");
    $data = V_WHS_MASTER::where("DSTRCT_CODE", "LIKE", "%$search%")
        ->orderBy("TABLE_CODE", 'asc')->get();
    if ($data->isEmpty()) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});
//end uncatagorized//

//processing//
Route::get("/GetDistByStkcd", function (Request $request) {
    $search = $request->input("1");
    $data = DB::connection('MSADMIN')->table('V_ITEMASTER_SCM')
        ->select(DB::raw('distinct DSTRCT_CODE'))
        ->where("STOCK_CODE", "LIKE", "%$search%")
        ->get();

    if (empty($data)) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});

Route::get("/GetWHSbyDsctStck", function (Request $request) {
    $stckcd = $request->input("1");
    $dstrct = $request->input("2");
    $data =  DB::connection('MSADMIN')->table('V_ITEMASTER_SCM as a')
        ->select(DB::raw('DISTINCT a.WHOUSE_ID, trim(b.table_desc) table_desc'))
        ->leftJoin('v_whs_master as b', function ($join) {
            $join->on(DB::raw('TRIM(a.WHOUSE_ID)'), '=', DB::raw('TRIM(b.table_code)'))
                ->on('a.dstrct_code', '=', 'b.dstrct_code');
        })
        ->where('a.STOCK_CODE', 'LIKE', "%$stckcd%")
        ->where('a.DSTRCT_CODE', 'LIKE', "%$dstrct%")
        ->get();

    if (empty($data)) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});

Route::get("/GetBinCodeLatest", function (Request $request) {
    $stckcd = $request->input("1");
    $dstrct = $request->input("2");
    $whs = $request->input("3");
    $data = DB::connection('MSADMIN')->table('V_ITEMASTER_SCM')
        ->select(DB::raw('distinct BIN_CODE'))
        ->where("STOCK_CODE", "LIKE", "%$stckcd%")
        ->where("DSTRCT_CODE", "LIKE", "%$dstrct%")
        ->where("WHOUSE_ID", "LIKE", "%$whs%")
        ->get();

    if (empty($data)) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});

Route::get("/GetSOHCurrent", function (Request $request) {
    $stckcd = $request->input("1");
    $dstrct = $request->input("2");
    $whs = $request->input("3");
    $bin = $request->input("4");
    $data = DB::connection('MSADMIN')->table('V_ITEMASTER_SCM')
        ->select(DB::raw('SOH'))
        ->where("STOCK_CODE", "LIKE", "%$stckcd%")
        ->where("DSTRCT_CODE", "LIKE", "%$dstrct%")
        ->where("WHOUSE_ID", "LIKE", "%$whs%")
        ->where("BIN_CODE", "LIKE", "%$bin%")
        ->get();

    if (empty($data)) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});

Route::get("/ValidateBin", function (Request $request) {
    $bininput = $request->input("1");
    $data = DB::connection('MSADMIN')->table('V_ITEMASTER_SCM')
        ->select(DB::raw('distinct BIN_CODE'))
        ->where("BIN_CODE", "LIKE", "%$bininput%")
        ->get();

    if (empty($data)) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});

Route::post("/saveproclog1", function (Request $request) {
    try {
        $data = new PROC_LOG_1();
        $data->STOCK_CODE = $request->input("STOCK_CODE");
        $data->DSTRCT_CODE = $request->input("DSTRCT_CODE");
        $data->WHOUSE_ID = $request->input("WHOUSE_ID");
        $data->BIN_LOCATION_FROM = $request->input("BIN_LOCATION_FROM");
        $data->BIN_LOCATION_TO = $request->input("BIN_LOCATION_TO");
        $data->created_by = $request->input("created_by");
        $data->QTY = $request->input("QTY");
        $data->save();

        return response()->json(["message" => "Data saved successfully"], 201);
    } catch (\Exception $e) {
        return response()->json(["error" => "Unable to save data"], 500);
    }
});
//end processing//

//issuing//
Route::get("/getissuing", function (Request $request) {
    $ireq = $request->input("1");
    $dstrctCode = $request->input("2");
    $role = $request->input("3");
    $dstrctCodereq = $request->input("4");

    $query = V_ISSUING_IR_SCM::query();
    if ($ireq) {
        $query->where("DOCUMENTNUMBER", "LIKE", "%$ireq%");
    }
    if ($role != "ALL") {
        if ($dstrctCode) {
            $query->where("DSTRCT_CODE", "LIKE", "%$dstrctCode%");
        }
    } else {
        if ($dstrctCode != $dstrctCodereq) {
            $query->where("DSTRCT_CODE", "LIKE", "%$dstrctCodereq%");
        }
    }

    $data = $query->get();
    if ($data->isEmpty()) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});
//select still_outstanding from V_ISSUING_IR_SCM where documentnumber = 'H01925' and stock_code = '200041112' and bin_code = 'FT01.030.003'
Route::get("/getqtypending", function (Request $request) {
    $documentNumber = $request->input('1');
    $stockCode = $request->input('2');
    $binCode = $request->input('3');

    // Query the database
    $result = V_ISSUING_IR_SCM::where('documentnumber', $documentNumber)
        ->where('stock_code', $stockCode)
        ->where('bin_code', $binCode)
        ->value('still_outstanding');

    if ($result !== null) {
        return response()->json(['still_outstanding' => $result]);
    } else {
        return response()->json(['message' => 'No data found']);
    }
});
Route::post("/saveissulogdtl", function (Request $request) {
    try {
        $maxId = ISSU_TRA_HDR::max('id');
        $maxIdRow = ISSU_TRA_HDR::where('id', $maxId)->first();
        if ($maxIdRow !== null) {
            // Access the requester ID from the row
            $rqstr = (float) $maxIdRow->requester;
        } else {
            // Handle the case where there's no data with the maximum 'id'
            $rqstr = 0.0; // or any other default value
        }
        $data = new ISSU_LOG_DTL();
        $data->STOCK_CODE = $request->input("STOCK_CODE");
        $data->QTY = $request->input("QTY");
        $data->BIN_LOCATION_FROM = $request->input("BIN_LOCATION_FROM");
        $data->created_by = $request->input("created_by");
        $data->DSTRCT_CODE =  $request->input("DSTRCT_CODE");
        $data->REQUESTER = (float)$rqstr;
        $data->ISSU_TOKEN = (float)$maxId;
        $data->save();

        return response()->json(["message" => "Data saved successfully"], 201);
    } catch (\Exception $e) {
        return response()->json(["error" => "Unable to save data"], 500);
    }
});

Route::post("/saveissuloghdr", function (Request $request) {
    try {
        $data = new ISSU_LOG_HDR();
        $data->IR_REQ = $request->input("IR_REQ");
        $data->REQUESTER = $request->input("REQUESTER");
        $data->created_by = $request->input("created_by");
        $data->DSTRCT_CODE = $request->input("DSTRCT_CODE");
        $data->save();

        return response()->json(["message" => "Data saved successfully"], 201);
    } catch (\Exception $e) {
        return response()->json(["error" => "Unable to save data"], 500);
    }
});

Route::post("/saveissutradtl", function (Request $request) {
    try {
        $maxId = ISSU_TRA_HDR::max('id');
        $maxIdRow = ISSU_TRA_HDR::where('id', $maxId)->first();

        if ($maxIdRow !== null) {
            // Access the requester ID from the row
            $rqstr = (int) $maxIdRow->requester;
        } else {
            // Handle the case where there's no data with the maximum 'id'
            $rqstr = 0; // or any other default value
        }
        $data = new ISSU_TRA_DTL();
        $data->STOCK_CODE = $request->input("STOCK_CODE");
        $data->QTY = $request->input("QTY");
        $data->BIN_LOCATION_FROM = $request->input("BIN_LOCATION_FROM");
        $data->created_by = $request->input("created_by");
        $data->REQUESTER = (int)$rqstr;
        $data->ISSU_TOKEN = (int)$maxId;
        $data->DSTRCT_CODE =  $request->input("DSTRCT_CODE");
        $data->save();

        return response()->json(["message" => "Data saved successfully"], 201);
    } catch (\Exception $e) {
        //return response()->json(["error" => "Unable to save data"], 500);
        return response()->json(["error" => $e->getMessage()], 500);
    }
});

Route::post("/saveissutrahdr", function (Request $request) {
    try {
        $data = new ISSU_TRA_HDR();
        $data->IR_REQ = $request->input("IR_REQ");
        $data->REQUESTER = $request->input("REQUESTER");
        $data->created_by = $request->input("created_by");
        $data->DSTRCT_CODE = $request->input("DSTRCT_CODE");
        $data->save();

        return response()->json(["message" => "Data saved successfully"], 201);
    } catch (\Exception $e) {
        return response()->json(["error" => "Unable to save data"], 500);
    }
});

Route::get("/getfinalco", function (Request $request) {
    //$idtoken = $request->input("1"); // Use "id" as the parameter name
    $maxId = ISSU_TRA_HDR::max('id');
    if ($maxId !== null) {
        $id = (int) $maxId;
    } else {
        return response()->json(["message" => "Something went wrong, please try again from input IREQ"]);
    }
    $subquery = DB::connection('MSADMIN')->table('V_ITEMASTER_SCM')
        ->select('stock_code', 'description')
        ->distinct();

    // Main query to join with ISSU_TRA_DTL and filter by issu_token
    $data = DB::table('ISSU_TRA_DTL as a')
        ->joinSub($subquery, 'b', function ($join) {
            $join->on('a.stock_code', '=', 'b.stock_code');
        })
        ->select('a.stock_code', 'b.description', 'a.bin_location_from', 'a.qty')
        ->where('a.ISSU_TOKEN', '=', $id)
        ->get();

    if ($data->isEmpty()) {
        return response()->json(["message" => "You have not performed the issuing process."]);
    }
    return response()->json(["data" => $data]);
});

Route::put("/updateisuqty", function (Request $request) {
    try {
        $stockCode = $request->input('1');
        $binLocationFrom = $request->input('2');
        $issuToken = $request->input('3');
        $newQty = $request->input('4');

        $affectedRows = DB::table('ISSU_TRA_DTL')
            ->where('stock_code', $stockCode)
            ->where('bin_location_from', $binLocationFrom)
            ->where('issu_token', (int)$issuToken)
            ->update(['qty' => (int)$newQty, 'updated_at' => now()]);

        if ($affectedRows > 0) {
            return response()->json(['message' => 'Records updated successfully']);
        } else {
            return response()->json(['message' => 'No matching records found']);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});


//end issuing//

//receiving//
Route::get("/getreceiving", function (Request $request) {
    $pono = $request->input("1");
    $dstrct = $request->input("2");
    $role = $request->input("3");
    $query = V_PO_RCV::query();
    if ($pono) {
        $query->where("PO_NO", "LIKE", "%$pono%");
    }
    if ($role != "ALL") {
        if ($dstrct) {
            $query->where("DSTRCT_CODE", "LIKE", "%$dstrct%");
        }
    }

    $data = $query->get();
    if ($data->isEmpty()) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});

Route::get("/getreceiving2", function (Request $request) {
    $pono = $request->input("1");
    $dstrct = $request->input("2");
    $stckcd = $request->input("3");
    $query = V_PO_RCV::query();
    if ($pono) {
        $query->where("PO_NO", "LIKE", "%$pono%");
    }

    if ($stckcd) {
        $query->where("PREQ_STK_CODE", "LIKE", "%$stckcd%");
    }

    if ($dstrct) {
        $query->where("DSTRCT_CODE", "LIKE", "%$dstrct%");
    }


    $data = $query->get();
    if ($data->isEmpty()) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});

Route::get("/getwhs", function (Request $request) {
    $whs = $request->input("1");
    $dstrct = $request->input("2");
    $query = V_WHS_MASTER::query();
    if ($whs) {
        $query->where("TABLE_DESC", "LIKE", "%$whs%");
        $query->where("DSTRCT_CODE", "LIKE", "%$dstrct%");
    }
    // if ($role != "ALL"){
    //     if ($dstrct) {
    //         $query->where("DSTRCT_CODE", "LIKE", "%$dstrct%");
    //     }
    // }

    $data = $query->get();
    if ($data->isEmpty()) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});

Route::get("/validatercv", function (Request $request) {
    $pono = $request->input("1");
    $stkcd = $request->input("2");
    $qtyrecv = $request->input("3");
    $query = V_PO_RCV::query();
    if ($pono) {
        $query->where("PO_NO", "LIKE", "%$pono%");
    }
    if ($stkcd) {
        $query->where("PREQ_STK_CODE", "LIKE", "%$stkcd%");
    }
    if ($qtyrecv) {
        if (is_numeric($qtyrecv)) {
            $query->where("QTY_OPEN", ">=", $qtyrecv);
        } else {
            return response()->json(["message" => "Invalid quantity value"]);
        }
    }

    $data = $query->get();
    if ($data->isEmpty()) {
        return response()->json(["message" => "No data found"]);
    }
    return response()->json(["data" => $data]);
});

Route::post("/savercvlog1", function (Request $request) {
    try {
        $data = new RCV_LOG_1();
        $data->po_no = $request->input("PO_NO");
        $data->created_by = $request->input("created_by");
        $data->save();

        return response()->json(["message" => "Data saved successfully"], 201);
    } catch (\Exception $e) {
        return response()->json(["error" => "Unable to save data"], 500);
    }
});

Route::post("/savercvlog2", function (Request $request) {
    try {
        $data = new RCV_LOG_2();
        $data->po_no = $request->input("PO_NO");
        $data->stock_code = $request->input("STOCK_CODE");
        $data->bin_location = $request->input("BIN_LOCATION");
        $data->receipt_qty = $request->input("RECEIPT_QTY");
        $data->created_by = $request->input("created_by");
        $data->RECPT_REFF = $request->input("RECPT_REFF");
        $data->save();

        return response()->json(["message" => "Data saved successfully"], 201);
    } catch (\Exception $e) {
        return response()->json(["error" => $e->getMessage()], 500);
    }
});

Route::get('/dataPersonal', [EmployeeController::class, 'DataPersonal']);
Route::get('/historyLeaveEmployee', [LeaveController::class, 'HistoryLeaveEmployee']);
Route::get('/balanceLeaveEmployee', [LeaveController::class, 'BalanceLeaveEmployee']);
Route::get('/historyAbsenceEmployee', [AbsenceController::class, 'HistoryAbsenceEmployee']);

Route::post('/ad/login', [ActiveDirectoryController::class, 'login']);
//end receiving//
