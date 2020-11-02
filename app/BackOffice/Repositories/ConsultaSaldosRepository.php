<?php

namespace App\BackOffice\Repositories;

use App\BackOffice\Models\ConsultaSaldos;
use App\Parameter;

use Carbon\Carbon;

class ConsultaSaldosRepository  {

    public function __construct( 
        ConsultaSaldos $model,
        Parameter $parameterModel
        ) {
      $this->model = $model;
      $this->parameterModel = $parameterModel;

    }
  
    public function all($share) {
        $data = $this->model->query()->where('co_cli', $share)->get();
        $cacheValidMinsParameter = $this->parameterModel->query()->where('parameter', 'CACHE_VALID_MINS')->first();
        $newArray = array();
        $fechaCache = \DB::connection('sqlsrv_backoffice')->select("SELECT DATEDIFF(MINUTE, MAX(dCreated), GETDATE()) minutes  from  GARITA_CONSULTASALDOS where co_cli='".$share."'");
       
        //dd('fechacache '.$fechaCache[0]->minutes.'-- cacheValidMinsParameter '.$cacheValidMinsParameter->value );
        if(!$fechaCache[0]->minutes) {
            return response()->json([
                'success' => false,
                'message' => 'En estos momentos la informacion no esta disponible'
            ])->setStatusCode(400);
        }

        if($fechaCache[0]->minutes > $cacheValidMinsParameter->value ) {
             return response()->json([
                 'success' => false,
                 'message' => 'En estos momentos la informacion no esta disponible'
             ])->setStatusCode(400);
         }
        $acumulado = 0;
        foreach ($data as $key => $value) {
                $monto = $value->saldo;
                $acumulado = bcadd($acumulado, $monto, 2);
                $data[$key]->acumulado = number_format((float)$acumulado,2);
                
                $data[$key]->originalAmount = $value->saldo;
                $data[$key]->saldo = number_format((float)$value->saldo,2);
                $data[$key]->total_fac = number_format((float)$value->total_fac,2);
        }
        return response()->json([
            'success' => true,
            'message' => 'Temporal',
            'data' => $data,
            'cache' => true,
        ]);
    }

    public function deleteAndInsert($data) {
        $user = auth()->user()->group_id;
        $this->model->query()->where('co_cli', $user)->delete();
        foreach ($data as $key => $value) {
            $this->model->create([
                'co_cli' => $value->co_cli, 
                'fact_num' => $value->fact_num, 
                'fec_emis' => $value->fec_emis, 
                'fec_venc' => $value->fec_vence, 
                'descrip' => $value->descrip, 
                'saldo' => $value->saldo, 
                'total_fac' => $value->total_fac, 
                'fec_emis_fact' => $value->fec_emis_fact, 
                'co_cli2' => $value->co_cli2, 
                // 'portal_nroComprobante' => $value->portal_nroComprobante, 
                // 'portal_canalPago' => $value->portal_canalPago, 
                // 'portal_fec_pago' => $value->portal_fec_pago,
                'dCreated' => Carbon::now(),
            ]);
        }
    }

}