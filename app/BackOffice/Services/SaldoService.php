<?php

namespace App\BackOffice\Services;

use App\BackOffice\Repositories\SaldoRepository;

class SaldoService {

	public function __construct(SaldoRepository $repository) {
		$this->repository = $repository ;
	}

	public function index($share) {
		return $this->repository->all($share);
    }

}