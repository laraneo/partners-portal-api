<?php

namespace App\BackOffice\Repositories;

use App\BackOffice\Models\BancoReceptor;

class BancoReceptorRepository  {
  
    protected $post;

    public function __construct( BancoReceptor $model ) {
      $this->model = $model;

    }
    
    public function find($id) {
      return $this->model->find($id);
    }
  
    public function all() {
      $data = \DB::connection('sqlsrv_backoffice')->select(" SELECT * from portalpagos_BancoReceptor where UPPER(cNombreBanco) NOT LIKE '%EFECTIVO%'  ORDER BY cNombreBanco ASC");
      return $data;
    }
}