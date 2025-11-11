<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_LOG_1 extends Model
{
    use HasFactory;
    protected $connection   = 'MSADMIN';
    protected $table        = 'PROC_LOG_1';
    protected $fillable     = [
        'ID',
        'STOCK_CODE',
        'DSTRCT_CODE',
        'WHOUSE_ID',
        'BIN_LOCATION_FROM',
        'BIN_LOCATION_TO',
        'CREATED_BY',
        'UPDATED_BY',
        'CREATED_AT',
        'UPDATED_AT'
    ];
}
