<?php

namespace App\BackOffice\Services;

use App\BackOffice\Repositories\TasaCambioRepository;
use Illuminate\Http\Request;

class TasaCambioService {

	public function __construct(TasaCambioRepository $repository) {
		$this->repository = $repository ;
	}

	public function index() {
		return $this->repository->all();
	}

}