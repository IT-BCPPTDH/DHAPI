<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class V_WHS_MASTER extends Model
{
    use HasFactory;
    protected $connection   = 'MSADMIN';
    protected $table        = 'V_WHS_MASTER';
    protected $fillable = ['TABLE_CODE','TABLE_DESC','DSTRCT_CODE'];
}
