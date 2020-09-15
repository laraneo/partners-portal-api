<?php

namespace App\BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class LoginToken extends Model
{
    protected $connection = "sqlsrv_backoffice";
    protected $table = 'portalpagos_LoginToken';
    protected $fillable = ['Login', 'token', 'expiration'];
    public $timestamps = false;
}
