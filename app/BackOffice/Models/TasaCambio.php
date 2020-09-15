<?php

namespace App\BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class TasaCambio extends Model
{
    protected $connection = "sqlsrv_backoffice";
    protected $table = 'portalpagos_TasaCambio';
    protected $fillable = [
        'co_mone',
        'dFecha', 
        'dTasa',
        'dCreated',
    ]; 
    public $timestamps = false;
}
