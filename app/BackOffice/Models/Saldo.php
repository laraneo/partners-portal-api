<?php

namespace App\BackOffice\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Saldo extends Model
{
    protected $connection = "sqlsrv_backoffice";
    protected $table = 'portalpagos_Saldo';
    protected $fillable = [
        'co_cli', 
        'saldo', 
        'status', 
        'dCreated',
        'saldo_vigencia'
    ];

    public $timestamps = false;
}
