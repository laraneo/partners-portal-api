<?php

namespace App\Services;

use App\Repositories\RecordRepository;
use Illuminate\Http\Request;

class RecordService {

	public function __construct(RecordRepository $repository) {
		$this->repository = $repository ;
	}

	public function index($perPage) {
		return $this->repository->all($perPage);
	}

	public function getList() {
		return $this->repository->getList();
	}

	public function create($request, $other) {
		if ($this->repository->checkRecord($request['description'])) {
            return response()->json([
                'success' => false,
                'message' => 'El registro ya existe'
            ])->setStatusCode(400);
		}
		// $file = -$other->file('file1')->getClientOriginalName();
		// $other->file('file1')->move(public_path('storage/partners/') . $file);
		// $request['file1'] = 'test';
		return $this->repository->create($request);
	}

	public function update($request, $id) {
      return $this->repository->update($id, $request);
	}

	public function read($id) {
     return $this->repository->find($id);
	}

	public function delete($id) {
      return $this->repository->delete($id);
	}

	/**
	 *  Search resource from repository
	 * @param  object $queryFilter
	*/
	public function search($queryFilter) {
		return $this->repository->search($queryFilter);
	 }
	 
	/**
	 *  Search resource from repository
	 * @param  object $queryFilter
	*/
	public function getByPerson($queryFilter) {
		return $this->repository->getByPerson($queryFilter);
	 }
	 
	 	/**
	 *  Search resource from repository
	 * @param  object $queryFilter
	*/
	public function getRecordsStatistics() {
		return $this->repository->getRecordsStatistics();
 	}
}