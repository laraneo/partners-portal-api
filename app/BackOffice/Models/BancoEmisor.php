<?php

namespace App\BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class BancoEmisor extends Model
{
    protected $connection = "sqlsrv_backoffice";
    protected $table = 'portalpagos_BancoEmisor';
    protected $fillable = ['cCodBanco','cNombreBanco']; 
    public $timestamps = false;
}
