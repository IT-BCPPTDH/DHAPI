<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ISSU_LOG_1 extends Model
{
    use HasFactory;
    protected $connection   = 'MSADMIN';
    protected $table        = 'ISSU_LOG_1';
    protected $fillable     = [
        'ID',
        'STOCK_CODE',
        'QTY',
        'BIN_LOCATION_FROM',
        'CREATED_BY',
        'UPDATED_BY',
        'CREATED_AT',
        'UPDATED_AT'
    ];
}
