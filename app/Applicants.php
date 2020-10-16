<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Applicants extends Model
{
    protected $table = 'portalpagos_applicants';
    protected $fillable = [
        'sCI', 
        'sNombres', 
        'sApellidos', 
        'sArchivo', 
        'nStatus', 
        'dCreated',
        'picture'
    ];

    public $timestamps = false;
}
