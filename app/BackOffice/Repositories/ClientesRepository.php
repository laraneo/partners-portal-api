<?php

namespace App\BackOffice\Repositories;

use App\BackOffice\Models\Clientes;

class ClientesRepository  {
  
    protected $post;

    public function __construct( Clientes $model ) {
      $this->model = $model;

    }
  
    public function all() {
      return $this->model->all();
    }

    public function findByNit($share) {
        return $this->model->query()->select(['co_cli', 'cli_des'])->where('nit', $share)->first();
    }
}