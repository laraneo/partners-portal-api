<?php

namespace App\BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class ReportePagos extends Model
{
    protected $connection = "sqlsrv_backoffice";
    protected $table = 'portalpagos_ReportePagos';
    protected $fillable = [
        'idPago', 
        'nMonto', 
        'NroReferencia',
        'sDescripcion',
        'EstadoCuenta',
        'status',
        'dFechaProceso',
        'Login',
        'Archivos',
        'codBancoOrigen',
        'codCuentaDestino',
        'NroReferencia2',
        'dFechaRegistro',
        'dFechaPago',
        'Moneda',
        'Nota',
        'fact_num',
        'fact_date',
        'dateSync',
        'isSync',
        'dCreated',
        'nTasa',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cuenta()
    {
        return $this->belongsTo('App\BackOffice\Models\BancoReceptor', 'codCuentaDestino', 'cCodCuenta');
    }

            /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bancoOrigen()
    {
        return $this->belongsTo('App\BackOffice\Models\BancoEmisor', 'codBancoOrigen', 'cCodBanco');
    }

    public $timestamps = false;
}
