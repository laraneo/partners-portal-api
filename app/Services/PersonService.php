<?php

namespace App\Services;

use App\Repositories\PersonRepository;
use App\Repositories\ProfessionRepository;
use App\Repositories\PersonProfessionRepository;
use App\Repositories\PersonCountryRepository;
use App\Repositories\PersonSportRepository;
use App\Repositories\PersonLockerRepository;
use App\Repositories\PersonExceptionRepository;
use App\Repositories\ShareRepository;
use App\Repositories\PersonRelationRepository;
use App\BackOffice\Repositories\ReportePagosRepository;

use Illuminate\Http\Request;
use Carbon\Carbon;

class PersonService {

	public function __construct(
		PersonRepository $person, 
		PersonProfessionRepository $personProfessionRepository,
		PersonCountryRepository $personCountryRepository,
		PersonSportRepository $personSportRepository,
		PersonLockerRepository $personLockerRepository,
		PersonExceptionRepository $personExceptionRepository,
		ShareRepository $shareRepository,
		PersonRelationRepository $personRelationRepository,
		ReportePagosRepository $reportePagosRepository
		) {
		$this->person = $person;
		$this->personProfessionRepository = $personProfessionRepository;
		$this->personCountryRepository = $personCountryRepository;
		$this->personSportRepository = $personSportRepository;
		$this->personLockerRepository = $personLockerRepository;
		$this->personExceptionRepository = $personExceptionRepository;
		$this->shareRepository = $shareRepository;
		$this->personRelationRepository = $personRelationRepository;
		$this->reportePagosRepository = $reportePagosRepository;
	}

	public function index($perPage) {
		return $this->person->all($perPage);
	}

	public function getPartners($perPage) {
		return $this->person->getPartners($perPage);
	}

	public function reportAll() {
		return $this->person->reportAll();
	}

	public function create($request) {
		if ($this->person->checkPerson($request['rif_ci'])) {
            return response()->json([
                'success' => false,
                'message' => 'Person already exist'
            ])->setStatusCode(400);
		}
		$image = $request['picture'];
		if($image !== null) {
			\Image::make($request['picture'])->save(public_path('storage/partners/').$request['rif_ci'].'.png');
			$request['picture'] = $request['rif_ci'].'.png';
		} else {
			$request['picture'] = "partner-empty.png";
		}
		$response = $this->person->create($request);
		if($request['isPartner'] && $request['isPartner'] === 2){
			$attr = [ 
				'base_id' =>  $request['base_id'], 
				'related_id' =>  $response->id, 
				'relation_type_id' =>  $request['relation_type_id'],
				'status' =>  1,
			];
			$this->personRelationRepository->create($attr);
		}
		if ($response) {
			$partner = $this->person->checkPerson($request['rif_ci']);
			$professions = json_decode($request['profession_list']);
			return response()->json([
				'success' => true,
				'data' => $partner
			]);
		}

	}

	public function update($request, $id) {
	$image = $request['picture'];
	if (substr($image, 0, 4) === "http" ) {
		$request['picture'] = $request['rif_ci'].'.png';
	} else {
		if($image !== null) {
			\Image::make($request['picture'])->save(public_path('storage/partners/').$request['rif_ci'].'.png');
			$request['picture'] = $request['rif_ci'].'.png';
		} else {
			$request['picture'] = "partner-empty.png";
		}
	}
	if ($request['profession_list'] !== null && $request['profession_list']) {
		$professions = json_decode($request['profession_list']);
		if(count($professions)) {
			if($this->personProfessionRepository->findPartner($id)){
				$this->personProfessionRepository->deleteRegistersbyPerson($id);
			}
			foreach ($professions as $profession) {
				$data = ['people_id' => $id, 'profession_id' => $profession];
				$this->personProfessionRepository->create($data);
			}
		}
	}

	if ($request['country_list'] !== null && $request['country_list']) {
		$countries = json_decode($request['country_list']);
		if(count($countries)) {
			if($this->personCountryRepository->findPartner($id)){
				$this->personCountryRepository->deleteRegistersbyPerson($id);
			}
			foreach ($countries as $country) {
				$data = ['people_id' => $id, 'countries_id' => $country];
				$this->personCountryRepository->create($data);
			}
		}
	}

	if ($request['sport_list'] !== null && $request['sport_list']) {
		$sports = json_decode($request['sport_list']);
		if(count($sports)) {
			if($this->personSportRepository->findPartner($id)){
				$this->personSportRepository->deleteRegistersbyPerson($id);
			}
			foreach ($sports as $sport) {
				$data = ['people_id' => $id, 'sports_id' => $sport];
				$this->personSportRepository->create($data);
			}
		}
	}

	if ($request['lockers'] !== null && $request['lockers']) {
		$lockers = $request['lockers'];
		if(count($lockers['itemsToAdd'])) {
			foreach ($lockers['itemsToAdd'] as $itemsToAdd) {
				$locker = $this->personLockerRepository->find($id, $itemsToAdd['id']);
				if(!$locker) {
					$data = ['people_id' => $id, 'locker_id' => $itemsToAdd['id']];
					$this->personLockerRepository->create($data);
				}
			}
		}

		if(count($lockers['itemsToRemove'])) {
			foreach ($lockers['itemsToRemove'] as $itemsToRemove) {
				$locker = $this->personLockerRepository->find($id, $itemsToRemove['id']);
				if($locker) {
					$this->personLockerRepository->delete($locker->id);
				}
			}
		}
	}

      return $this->person->update($id, $request);
	}

	public function read($id) {
     return $this->person->find($id);
	}

	public function delete($id) {
      return $this->person->delete($id);
	}

	/**
	 *  Search resource from repository
	 * @param  object $queryFilter
	*/
	public function search($queryFilter) {
		return $this->person->search($queryFilter);
	 }

	 	/**
	 *  Search resource from repository
	 * @param  object $queryFilter
	*/
	public function searchByPartners($queryFilter) {
		return $this->person->searchByPartners($queryFilter);
	 }
	 

	/**
	 *  Search resource from repository
	 * @param  object $queryFilter
	*/
	public function filter($queryFilter, $isPDF = false) {
		return $this->person->filter($queryFilter, $isPDF);
	 }
	 
    /**
	 *  Search resource from repository
	 * @param  object $queryFilter
	*/
	public function searchByCompany($queryFilter) {
		return $this->person->searchByCompany($queryFilter);
	 }
	 
	 	/**
	 *  Search resource from repository
	 * @param  object $queryFilter
	*/
	public function searchPersonsToAssign($queryFilter) {
		return $this->person->searchPersonsToAssign($queryFilter);
	 }

	public function assignPerson($attributes) {
		$this->person->assignPerson($attributes);
	}

	    /**
	 *  Search resource from repository
	 * @param  object $queryFilter
	*/
	public function searchFamilyByPerson($queryFilter) {
		return $this->person->searchFamilyByPerson($queryFilter);
	 }

	 public function getReportByPartner($id) {
		return $this->person->getReportByPartner($id);
	   }

	   	/**
	 *  Search resource from repository
	 * @param  object $queryFilter
	*/
	public function searchToAssign($queryFilter) {
		return $this->person->searchToAssign($queryFilter);
	 }
	
	public function getFamiliesPartnerByCard($card){
		return $this->person->getFamiliesPartnerByCard($card);
	}

	public function getGuestByPartner($identification){
		return $this->person->getGuestByPartner($identification);
	}

	public function getLockersByLocation($request) {
		return $this->person->getLockersByLocation($request);
	}

	public function getLockersByPartner($id) {
		return $this->person->getLockersByPartner($id);
	}

	public function getCountPersons(){
		return $this->person->getCountPersons();
	}

	public function getCountPersonByIsPartner($isPartner) {
		return $this->person->getCountPersonByIsPartner($isPartner);
	}

	public function getExceptionStatistics() {
		return $this->personExceptionRepository->getStatistics();
	}

	public function getCountBirthdays() {
		return $this->person->getCountBirthdays();
	}

	public function getFamilyByPartner($share) {
		$shareUser = auth()->user()->username;
		$person = $this->shareRepository->findByShare($shareUser);
		return $this->person->getFamilyByPartner($person->partner->id);
	}

	public function validateFile($file) {

		$fileToParse = preg_replace('/^data:application\/\w+;base64,/', '', $file);
		$ext = explode(';', $file)[0];
		$ext = explode('/', $ext)[1];

		$find = 'data:application/vnd.openxmlformats-officedocument.wordprocessingml.document;base64,';
		$pos = strpos($file, $find);
		if($pos !== false) {
			$fileToParse = str_replace('data:application/vnd.openxmlformats-officedocument.wordprocessingml.document;base64,', '', $file);
			$ext = 'docx';
		}
		$base64File = base64_decode($fileToParse);
		
		return (object)['ext' => $ext, 'content' => $base64File];
	}

	public function createPaymentReport($attributes) {
		$date = Carbon::now()->format('Y-m-d');
		$attributes['dFechaRegistro'] = Carbon::now()->format('Y-m-d H:i:s');
		$data = $this->reportePagosRepository->create($attributes);
		if($attributes['file1'] !== null) {
			$parseFile = $this->validateFile($attributes['file1']);
			$filename = $date.'-'.$data->id.'.'.$parseFile->ext;
			$indice = rand(1,5);
			
			if($parseFile->ext === 'png' || $parseFile->ext === 'jpg' || $parseFile->ext === 'jpeg' ) {
				if($parseFile->ext === 'jpg' || $parseFile->ext === 'jpeg') {
					$filename = $date.'-'.$data->id.'.png';
				}
				\Image::make($attributes['file1'])->save(public_path('storage/partners/').$filename);
			} else {
				//Storage::disk('payments')->put($filename,$parseFile->content);
				\File::put(public_path(). '/storage/partners/' . $filename, $parseFile->content);
			}
			$attr = [ 'Archivos' => $filename, 'status' => $attributes['status']];
			$this->reportePagosRepository->update($data->id, $attr);
		}
		return $data;
	  }

}