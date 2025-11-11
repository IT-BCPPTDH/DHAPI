<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class V_ISSUING_IR_SCM extends Model
{
    use HasFactory;
    protected $connection   = 'MSADMIN';
    protected $table        = 'V_ISSUING_IR_SCM';
}
