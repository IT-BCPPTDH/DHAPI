<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class V_ITEMASTER_SCM extends Model
{
    use HasFactory;
    protected $connection   = 'MSADMIN';
    protected $table        = 'V_ITEMASTER_SCM';
    protected $fillable     = [
        'RN',
        'DSTRCT_CODE',
        'STOCK_CODE',
        'PART_NO',
        'DESCRIPTION',
        'CLASSIFICATION',
        'ABCD_LEVEL',
        'XYZ_LEVEL',
        'LAST_REC_DATE',
        'SUPPLIER_NO',
        'SUPPLIER_NAME',
        'LAST_ISS_DATE',
        'ROP',
        'ROQ',
        'MIN_STOCK_LVL',
        'WHOUSE_ID',
        'AVAILBILITY',
        'CONSIGN_SOH',
        'SOH',
        'DUES_OUT',
        'IN_TRANSIT',
        'DUES_IN',
        'PRICE',
        'BIN_CODE',
        'STOCK_TYPE',
        'REC_ORD_ONLINE'
   ];
}
