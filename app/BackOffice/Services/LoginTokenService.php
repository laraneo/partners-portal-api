<?php

namespace App\BackOffice\Services;

use App\BackOffice\Repositories\LoginTokenRepository;
use Illuminate\Http\Request;

class LoginTokenService {

	public function __construct(LoginTokenRepository $repository) {
		$this->repository = $repository ;
	}

	public function index() {
		return $this->repository->all();
    }
    
    public function find($partner, $token) {
		return $this->repository->find($partner,$token);
	}

}