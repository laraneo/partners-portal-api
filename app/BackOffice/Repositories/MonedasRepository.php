<?php

namespace App\BackOffice\Repositories;

use App\BackOffice\Models\Monedas;

class MonedasRepository  {
  
    protected $post;

    public function __construct( Monedas $model ) {
      $this->model = $model;

    }
    
    public function find($id) {
      return $this->model->find($id);
    }
  
    public function all() {
      return$this->model->all();
    }
}