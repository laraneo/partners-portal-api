<?php

namespace App\BackOffice\Repositories;

use App\BackOffice\Models\Saldo;
use App\Parameter;

use Carbon\Carbon;

class SaldoRepository  {

    public function __construct( 
        Saldo $model,
        Parameter $parameterModel
        ) {
      $this->model = $model;
      $this->parameterModel = $parameterModel;

    }
  
    public function all($share) {
        $data = $this->model->query()->where('co_cli', $share)->orderBy('dCreated', 'desc')->first();
        $cacheValidMinsParameter = $this->parameterModel->query()->where('parameter', 'CACHE_VALID_MINS')->first();
        $newArray = array();
        $fechaCache = \DB::connection('sqlsrv_backoffice')->select("SELECT DATEDIFF(MINUTE, MAX(dCreated), GETDATE()) minutes  from  portalpagos_Saldo where co_cli='".$share."'");
        
        //dd('fechacache '.$fechaCache[0]->minutes.'-- cacheValidMinsParameter '.$cacheValidMinsParameter->value );
        if(!$fechaCache[0]->minutes) {
            return response()->json([
                'success' => false,
                'message' => 'En estos momentos la informacion no esta disponible'
            ])->setStatusCode(400);
        } else {

        }

        if($fechaCache[0]->minutes > $cacheValidMinsParameter->value ) {
             return response()->json([
                 'success' => false,
                 'message' => 'En estos momentos la informacion no esta disponible'
             ])->setStatusCode(400);
         }
         $data->saldo = number_format((float)$data->saldo,2);
         $data->status = (string)$data->status;
        return response()->json([
            'success' => true,
            'message' => 'Temporal',
            'data' => [$data],
            'cache' => true,
        ]);
    }

    public function deleteAndInsert($data) {
        $user = auth()->user()->group_id;
        $this->model->query()->where('co_cli', $user)->delete();
        $this->model->create([
            'co_cli' => $user, 
            'saldo' => (float)$data->saldo,
            'saldo_vigencia' => $data->saldo_vigencia,
            'status' => $data->status, 
            'dCreated' => Carbon::now(),
        ]);
    }

}