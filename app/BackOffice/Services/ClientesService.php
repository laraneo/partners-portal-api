<?php

namespace App\BackOffice\Services;

use App\BackOffice\Repositories\ClientesRepository;
use Illuminate\Http\Request;

class ClientesService {

	public function __construct(ClientesRepository $repository) {
		$this->repository = $repository ;
	}

	public function index() {
		return $this->repository->all();
    }
    
    public function findByNit($share) {
		return $this->repository->findByNit($share);
	}

}