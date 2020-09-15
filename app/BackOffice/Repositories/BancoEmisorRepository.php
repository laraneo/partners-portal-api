<?php

namespace App\BackOffice\Repositories;

use App\BackOffice\Models\BancoEmisor;

class BancoEmisorRepository  {
  
    protected $post;

    public function __construct( BancoEmisor $model ) {
      $this->model = $model;

    }
    
    public function find($id) {
      return $this->model->find($id);
    }
  
    public function all() {
      $data = \DB::connection('sqlsrv_backoffice')->select(" SELECT * from portalpagos_BancoEmisor where UPPER(cNombreBanco) NOT LIKE '%EFECTIVO%'  ORDER BY cNombreBanco ASC");
      return $data;
    }
}