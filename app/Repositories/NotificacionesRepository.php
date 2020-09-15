<?php

namespace App\Repositories;

use App\Notificaciones;

class NotificacionesRepository  {
  
    protected $post;

    public function __construct(Notificaciones $model) {
      $this->model = $model;
    }

    public function create($attributes) {
      return $this->model->create($attributes);
    }
}