<?php

namespace App\BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    protected $connection = "sqlsrv_backoffice";
    protected $table = 'clientes';
    protected $fillable = [
        'co_cli', 
        'tipo', 
        'cli_des', 
        'direc1', 
        'direc2', 
        'telefonos', 
        'fax', 
        'nit'
    ];
    public $timestamps = false;
}
