<?php

namespace App\BackOffice\Repositories;

use App\BackOffice\Models\TasaCambio;

use Carbon\Carbon;

class TasaCambioRepository  {
  
    protected $post;

    public function __construct( TasaCambio $model ) {
      $this->model = $model;

    }
    
    public function find($id) {
      return $this->model->find($id);
    }

    public function store($attr) {
        $this->model->where('co_mone', 'US$')->delete();
        return $this->model->create($attr);
      }
  
    public function all() {
        $data = \DB::connection('sqlsrv_backoffice')->select("SELECT MAX(co_mone) co_mone, MAX(dFecha) fecha, MAX(dTasa) dTasa, MAX(dCreated) dCreated from portalpagos_TasaCambio where co_mone ='US$' ");
        if($data && $data[0] && $data[0]->co_mone !== NULL) {
          // dd('$data[0]->lastDate '.Carbon::parse($data[0]->lastDate)->format('Y-m-d').'date '.Carbon::now()->format('Y-m-d'));
            if(Carbon::parse($data[0]->fecha)->format('Y-m-d') === Carbon::now()->format('Y-m-d')) {
                return $data[0];
            }
        }
      return null;

    }
}