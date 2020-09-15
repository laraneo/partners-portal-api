<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notificaciones extends Model
{
    protected $connection = "sqlsrv_notificaciones";
    protected $table = 'Notificaciones';

    protected $fillable = [      
        'sFuente', 
        'sCorreo', 
        'sTelefono', 
        'sAsunto',
        'sDestinatario',
        'sAccion',
        'nStatus',
        'nTipo',
        'dFecha',
        'dFechaProgramada',
        'dFechaProcesada',
        'sArchivo',
        'sContenido',
        'sCuenta',
        'nIntentos',
        'sRespuesta',
    ];

    public function setdFechaAttribute() {
        $this->attributes['dFecha'] =  Carbon::now()->format('Y-m-d H:i:s');
    }

    public function setdFechaProgramadaAttribute() {
        $this->attributes['dFechaProgramada'] = Carbon::now()->format('Y-m-d H:i:s');
    }
    public $timestamps = false;
}
