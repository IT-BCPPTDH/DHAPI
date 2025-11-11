<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RCV_LOG_2 extends Model
{
    use HasFactory;
    protected $connection   = 'MSADMIN';
    protected $table        = 'RCV_LOG_2';
    protected $fillable     = [
         'id','po_no','stock_code','bin_location','receipt_qty',
          'created_by', 'updated_by', 'created_at', 'updated_at'
    ];
}
