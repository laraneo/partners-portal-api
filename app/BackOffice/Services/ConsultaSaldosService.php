<?php

namespace App\BackOffice\Services;

use App\BackOffice\Repositories\ConsultaSaldosRepository;

use Illuminate\Http\Request;
use Storage;
use Carbon\Carbon;


class ConsultaSaldosService {

	public function __construct(ConsultaSaldosRepository $repository) {
		$this->repository = $repository ;
	}

	public function index($share) {
		return $this->repository->all($share);
    }

}