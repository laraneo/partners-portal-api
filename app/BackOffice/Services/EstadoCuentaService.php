<?php

namespace App\BackOffice\Services;

use App\BackOffice\Repositories\EstadoCuentaRepository;

class EstadoCuentaService {

	public function __construct(EstadoCuentaRepository $repository) {
		$this->repository = $repository ;
	}

	public function index($share) {
		return $this->repository->all($share);
    }

}