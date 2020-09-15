<?php

namespace App\Services;

use App\Repositories\NotificacionesRepository;
use Illuminate\Http\Request;

class NotificacionesService {

	public function __construct(NotificacionesRepository $repository) {
		$this->repository = $repository ;
	}

	public function create($request) {
		return $this->repository->create($request);
	}

}