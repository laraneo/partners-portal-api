<?php

namespace App\BackOffice\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ConsultaSaldos extends Model
{
    protected $connection = "sqlsrv_backoffice";
    protected $table = 'GARITA_CONSULTASALDOS';
    protected $fillable = [
        'co_cli', 
        'fact_num', 
        'fec_emis', 
        'fec_venc', 
        'descrip', 
        'saldo', 
        'total_fac', 
        'fec_emis_fact', 
        'co_cli2', 
        'portal_nroComprobante', 
        'portal_canalPago', 
        'portal_fec_pago',
        'dCreated',
    ];

    public $timestamps = false;
}
