<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class V_PO_RCV extends Model
{
    use HasFactory;
    protected $connection   = 'MSADMIN';
    protected $table        = 'V_PO_RCV';
}
