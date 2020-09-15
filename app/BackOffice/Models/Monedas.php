<?php

namespace App\BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class Monedas extends Model
{
    protected $connection = "sqlsrv_backoffice";
    protected $table = 'portalpagos_Monedas';
    protected $fillable = [
        'co_mone',
        'mone_des', 
        'mone_iso'
    ]; 
    public $timestamps = false;
}
