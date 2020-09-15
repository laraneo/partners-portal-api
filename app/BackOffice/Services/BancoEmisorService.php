<?php

namespace App\BackOffice\Services;

use App\BackOffice\Repositories\BancoEmisorRepository;
use Illuminate\Http\Request;

class BancoEmisorService {

	public function __construct(BancoEmisorRepository $repository) {
		$this->repository = $repository ;
	}

	public function index() {
		return $this->repository->all();
	}

}