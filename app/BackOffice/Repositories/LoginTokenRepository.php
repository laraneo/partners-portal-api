<?php

namespace App\BackOffice\Repositories;

use App\BackOffice\Models\LoginToken;

class LoginTokenRepository  {
  
    protected $post;

    public function __construct( LoginToken $model ) {
      $this->model = $model;

    }
  
    public function all() {
      return $this->model->all();
    }

    public function find($partner, $token) {
        return $this->model->where('Login', $partner)->where('token', $token)->where('expiration' ,'>=' ,date('Y') )->first();
    }
}