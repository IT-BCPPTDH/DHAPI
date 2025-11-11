<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PRETESTController extends Controller
{
    public function getEmployee(){
        try {
            $data = DB::connection('MSADMIN')->table('TEST_EMPLOYEE')->get();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data retrieved successfully',
                'data'    => $data
            ], 200)->header('Access-Control-Allow-Origin', 'https://yourdomain.com')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');;

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve data',
                'error'   => $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', 'https://yourdomain.com')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');;
        }
    }

    public function getUnit(){
        try {
            $data = DB::connection('MSADMIN')->table('TEST_UNIT')->get();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data retrieved successfully',
                'data'    => $data
            ], 200)->header('Access-Control-Allow-Origin', 'https://yourdomain.com')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');;

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve data',
                'error'   => $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', 'https://yourdomain.com')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');;
        }
    }

    public function getUnitMapping(){
        try {
            $data = DB::connection('MSADMIN')->table('TEST_UNIT_MAPPING')->get();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data retrieved successfully',
                'data'    => $data
            ], 200)->header('Access-Control-Allow-Origin', 'https://yourdomain.com')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');;

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve data',
                'error'   => $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', 'https://yourdomain.com')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');;
        }
    }
}
