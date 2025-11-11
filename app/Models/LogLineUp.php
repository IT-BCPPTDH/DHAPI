<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogLineUp extends Model
{
    use HasFactory;
    protected $connection = 'BCPDWHS';
    protected $table  = 'LOGLINEUP';
    protected $fillable = [
        'id', 'date_lineup', 'jdeno', 'status', 'created_at', 'updated_at', 'address', 'device', 'platform'
    ];

}
