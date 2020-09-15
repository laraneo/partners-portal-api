<?php

namespace App\Services;

use App\Person;
use App\Repositories\ShareRepository;
use App\Repositories\AccessControlRepository;
use App\Repositories\ParameterRepository;
use App\Repositories\RecordRepository;
use Illuminate\Http\Request;

class AccessControlService {

	public function __construct(
		AccessControlRepository $repository,
		Person $personModel,
		ShareRepository $shareRepository,
		ParameterRepository $parameterRepository,
		RecordRepository $recordRepository
		) 
		{
		$this->repository = $repository;
		$this->personModel = $personModel;
		$this->shareRepository = $shareRepository;
		$this->parameterRepository = $parameterRepository;
		$this->recordRepository = $recordRepository;
	}

	public function index($perPage) {
		return $this->repository->all($perPage);
	}

	public function getList() {
		return $this->repository->getList();
	}

	public function filter($queryFilter, $isPDF = false) {
		return $this->repository->filter($queryFilter, $isPDF);
	}

	public function legacyAccesControlIngration($person, $type) {
		$person = $this->model->where('isPartner', $id)->first();
		if($person && $person->isPartner === $type) {
			return 1;
		}
		return 0;
	}

	public function checkPersonStatus($id) {
		$person = $this->personModel->where('id',$id)->with([
			'statusPerson' => function($query) {
				$query->select('id', 'description');
			}
		])->first();
		return $person->statusPerson->description;
	}

	function validatePartner($request) {
		$status = 1;
		$message = '';
		$records = $this->recordRepository->getBlockedRecord($request['people_id']);
		if(count($records)) {
			foreach ($records as $key => $value) {
				$status = $status - 4;
				$message .= '* Presenta bloqueo activo por expediente :'.$value->id.',  hasta la fecha  '.$value->expiration_date.'<br>';
			}
		}
		$share = $this->shareRepository->find($request['share_id']);
		if($share->status === 0) {
			$message .= '* Accion Inactiva <br>';
			$status = $status -4;
		}

		$personStatus = $this->checkPersonStatus($request['people_id']);

		if($personStatus === "Inactivo"){
			$message .= '* Socio Inactivo <br>';
			$status = $status -4;
		}
		$data = $this->repository->create($request);
		return $message;
	}

	public function validateGuest($request) {
		if($request['guest_id'] !== "") {
			$status = 1;
			$message = '';
			$parameter = $this->parameterRepository->findByParameter('MAX_MONTH_VISITS_GUEST');
			$visits = $this->repository->getVisitsByMont($request['guest_id']);
			$personStatus = $this->checkPersonStatus($request['guest_id']);
			if($personStatus === "Inactivo"){
				$message .= '* Invitado Inactivo <br>';
				$status = $status -2;
			}
			if(count($visits) > $parameter->value) {
				$message .= '* Persona  excede cantidad Maxima de visitas por Mes permitida : '.$parameter->value.'<br>';
				$status = $status - 8;
			}
			$request['guest_id'] = $request['guest_id'];
			$request['status'] = $status;
			$this->repository->create($request);
			return $message;
		}
	}

	public function create($request) {
		$status = 1;
		$message = '';

		$validatePartnerMessage = $this->validatePartner($request);
		if($validatePartnerMessage !== '') {
			$message.= '<strong>- Socio</strong>: <br>
			'.$validatePartnerMessage.'
			';
		} else {
			$this->legacyAccesControlIngration($request['people_id']);
		}

		if($request['family']) {
			$status = 1;
			foreach ($request['family'] as $element) {
				$request['people_id'] = $element;
				$request['status'] = $status;
				$this->repository->create($request);
			}
		}

		$validateGuestMessage = $this->validateGuest($request);
			if($validateGuestMessage !== '') {
			$message.= '<strong>- Invitado</strong>: <br>
			'.$validateGuestMessage.'
			';
		} else {
			$this->legacyAccesControlIngration($request['guest_id']);
		}

		if($message !== '') {
			$body = '<div>
			<div>Error de Ingreso por siguientes razones:<div/>
			<div>'.$message.'</div>
			</div>';
			return response()->json([
				'success' => false,
				'message' => $body,
			])->setStatusCode(400);
		}
		return $data;
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
	 
	 public function getPartnersFamilyStatistics() {
		return $this->repository->getPartnersFamilyStatistics();
	}

	public function getGuestStatistics() {
		return $this->repository->getGuestStatistics();
	}
}