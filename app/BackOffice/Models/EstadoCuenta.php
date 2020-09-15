<?php

namespace App\BackOffice\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EstadoCuenta extends Model
{
    protected $connection = "sqlsrv_backoffice";
    protected $table = 'portalpagos_EstadoCuenta';
    protected $fillable = [
        'co_cli', 
        'fact_num', 
        'fec_emis', 
        'descrip', 
        'total_fac', 
        'saldo',
        'tipo',
        'dCreated',
    ];

    public $timestamps = false;
}
