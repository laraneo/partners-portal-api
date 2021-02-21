<?php

namespace App\BackOffice\Repositories;

use App\BackOffice\Models\EstadoCuenta;
use App\Parameter;

use Carbon\Carbon;

class EstadoCuentaRepository  {

    public function __construct( 
        EstadoCuenta $model,
        Parameter $parameterModel
        ) {
      $this->model = $model;
      $this->parameterModel = $parameterModel;

    }
  
    public function all($share) {
        $data = $this->model->query()->where('co_cli', $share)->get();
        $cacheValidMinsParameter = $this->parameterModel->query()->where('parameter', 'CACHE_VALID_MINS')->first();
        $newArray = array();
        $fechaCache = \DB::connection('sqlsrv_backoffice')->select("SELECT DATEDIFF(MINUTE, MAX(dCreated), GETDATE()) minutes  from  portalpagos_EstadoCuenta where co_cli='".$share."'");
       
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
                
                $data[$key]->saldo = number_format((float)$value->saldo,2);
                $data[$key]->total_fac = number_format((float)$value->total_fac,2);
        }
        return response()->json([
            'success' => true,
            'message' => 'Temporal',
            'data' => $data,
            'cache' => true,
            'total' => $acumulado,
        ]);
    }

    public function deleteAndInsert($data) {
        $user = auth()->user()->group_id;
        $this->model->query()->where('co_cli', $user)->delete();
        foreach ($data as $key => $value) {
            $this->model->create([
                'co_cli' => $user, 
                'fact_num' => $value->fact_num, 
                'fec_emis' => Carbon::parse($value->fec_emis)->format('Y-m-d H:i:s'), 
                'descrip' => $value->descrip, 
                'total_fac' => (float)$value->total_fac, 
                'saldo' =>  (float)$value->saldo, 
                'tipo' => $value->tipo, 
                'dCreated' => Carbon::now(),
            ]);
        }
    }

}