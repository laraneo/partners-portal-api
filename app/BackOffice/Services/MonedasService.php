<?php

namespace App\BackOffice\Services;

use App\BackOffice\Repositories\MonedasRepository;
use Illuminate\Http\Request;

class MonedasService {

	public function __construct(MonedasRepository $repository) {
		$this->repository = $repository ;
	}

	public function index() {
		return $this->repository->all();
	}

}