<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PTDHCardController extends Controller
{
    public function GetCard(){
        $data           = DB::connection('BCPDWHS')->table('PTDHCARD AS A')
                            ->where('A.EMPLOYEE_JDENUMBER', '122650')
                            ->where('A.STATUS', 'ACTIVE')
                            ->first();


        return response()->json([
            'data'  => $data
        ]);
    }
}
