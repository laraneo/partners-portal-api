<?php

namespace App\BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class BancoReceptor extends Model
{
    protected $connection = "sqlsrv_backoffice";
    protected $table = 'portalpagos_BancoReceptor';
    protected $fillable = ['cCodBanco','cNombreBanco', 'cNumCuenta', 'cCodCuenta']; 
    public $timestamps = false;
}
