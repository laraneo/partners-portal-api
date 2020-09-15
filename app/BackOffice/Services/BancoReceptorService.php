<?php

namespace App\BackOffice\Services;

use App\BackOffice\Repositories\BancoReceptorRepository;
use Illuminate\Http\Request;

class BancoReceptorService {

	public function __construct(BancoReceptorRepository $repository) {
		$this->repository = $repository ;
	}

	public function index() {
		return $this->repository->all();
	}

}