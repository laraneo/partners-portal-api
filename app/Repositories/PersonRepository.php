<?php

namespace App\Repositories;

use App\Person;
use App\Share;
use App\PersonRelation;
use App\Repositories\ShareRepository;
use App\Repositories\RelationTypeRepository;
use App\Repositories\PersonRelationRepository;
use App\Repositories\AccessControlRepository;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class PersonRepository  {

    public function __construct(
      Person $model,
      PersonRelationRepository $personRelationRepository,
      RelationTypeRepository $relationTypeRepository,
      ShareRepository $shareRepository,
      AccessControlRepository $accessControlRepository,
      Share $shareModel
      )
      {
      $this->model = $model;
      $this->personRelationRepository = $personRelationRepository;
      $this->relationTypeRepository = $relationTypeRepository;
      $this->shareRepository = $shareRepository;
      $this->accessControlRepository = $accessControlRepository;
      $this->shareModel = $shareModel;
    }

    public function find($id) {
      $person = $this->model->where('id', $id)->with([
        'professions',
        'creditCards',
        'shares',
        'countries',
        'sports',
        'lockers',
        'companyPerson',
        'relationship'
      ])->first();
      if($relation = $person->isPartner == 1) {
        $relation = 'SOCIO';
      } else {
        $relation = $person->relationship ? $person->relationship()->with(['relationType'])->first() : '';
        $relation = $person->relationship ? $relation->relationType()->first()->description : '';
      }
      $person->relation = $relation;
      $person->picture = url('storage/partners/'.$person->picture);
      return $person;
    }

    public function create($attributes) {
      return $this->model->create($attributes);
    }

    public function update($id, array $attributes) {
      return $this->model->find($id)->update($attributes);
    }

    public function reportAll() {
      return $this->model->all();
    }



    public function all($perPage) {
      return $this->model->query()->whereIn('isPartner', [1, 2])->paginate($perPage);
    }

    public function getPartners($perPage) {
      $persons = $this->model->query()->with('shares')->whereIn('isPartner', [1,2])->paginate($perPage);
      foreach ($persons as $key => $value) {
        unset($persons[$key]->shares);
        $persons[$key]->shares = $this->parseShares($value->shares()->get());
      }
      return $persons;
    }

        /**
     * get persons by query params
     * @param  object $queryFilter
    */
    public function searchByPartners($queryFilter) {
      $search;
      if($queryFilter->query('term') === null) {
        $search = $this->model->query()->where('isPartner', 1)->paginate(8);
      } else {

        $searchQuery = trim($queryFilter->query('term'));
        $this->share = $queryFilter->query('term');
        $requestData = ['name', 'last_name', 'rif_ci'];
        $search = $this->model->with('shares')->where(function($q) use($requestData, $searchQuery) {
                    foreach ($requestData as $field) {
                       $q->orWhere($field, 'like', "%{$searchQuery}%");
                    }
                    $persons = $this->shareModel->query()->where('share_number','like', '%'.$this->share.'%')->get();
                    if(count($persons)) {
                      foreach ($persons as $key => $value) {
                        $q->orWhere('id', $value->id_persona);
                      }
                    }
                  })->whereIn('isPartner', [1,2])->paginate(8);
        foreach ($search as $key => $value) {
          unset($search[$key]->shares);
          $search[$key]->shares = $this->parseShares($value->shares()->get());
        }
      }
     return $search;
    }


    public function delete($id) {
     return $this->model->find($id)->delete();
    }

    public function checkPerson($name)
    {
      $person = $this->model->where('rif_ci', $name)->first();
      if ($person) {
        return $person;
      }
      return false;
    }

    /**
     * get persons by query params
     * @param  object $queryFilter
    */
    public function search($queryFilter) {
      $search;
      if($queryFilter->query('term') === null) {
        $search = $this->model->all();
      } else {
        $term = $queryFilter->query('term');
        $search = $this->model->query()->whereLike(['name', 'last_name'], $term)->where('rif_ci',$term)->get();
      }
     return $search;
    }

    /**
     * get persons by query params
     * @param  object $queryFilter
    */
    public function filter($queryFilter, $isPDF = false) {
      $search = $this->model->query()->with(['statusPerson', 'maritalStatus', 'gender', 'country','professions', 'shares',
      'relationship' => function($query){
        $query->select('id', 'related_id', 'base_id', 'relation_type_id')->with('relationType');
    },]);
      if ($queryFilter->query('isPartner')) {
        $search->where('isPartner', $queryFilter->query('isPartner'));
      }
      if ($queryFilter->query('name')) {
        $search->where('name', 'like', '%'.$queryFilter->query('name').'%');
        $search->orWhere('last_name', 'like', '%'.$queryFilter->query('name').'%');
      }
      if ($queryFilter->query('rif_ci')) {
        $search->orWhere('rif_ci', 'like', '%'.$queryFilter->query('rif_ci').'%');
      }
      if ($queryFilter->query('passport')) {
        $search->orWhere('passport', 'like', '%'.$queryFilter->query('passport').'%');
      }
      if ($queryFilter->query('type_person')) {
        $search->where('type_person', $queryFilter->query('type_person'));
      }
      if ($queryFilter->query('gender_id')) {
        $search->where('gender_id', $queryFilter->query('gender_id'));
      }
      if ($queryFilter->query('status_person_id')) {
        $search->where('status_person_id', $queryFilter->query('status_person_id'));
      }
      if ($queryFilter->query('card_number')) {
        $search->orWhere('card_number', 'like', '%'.$queryFilter->query('card_number').'%');
      }
      if ($queryFilter->query('primary_email')) {
        $search->orWhere('primary_email', 'like', '%'.$queryFilter->query('primary_email').'%');
      }
      if ($queryFilter->query('telephone1')) {
        $search->orWhere('telephone1', 'like', '%'.$queryFilter->query('telephone1').'%');
      }
      if ($queryFilter->query('phone_mobile1')) {
        $search->orWhere('phone_mobile1', 'like', '%'.$queryFilter->query('phone_mobile1').'%');
      }
      if ($queryFilter->query('expiration_start') && $queryFilter->query('expiration_end')) {
        $search->orWhereBetween('expiration_date', [$queryFilter->query('expiration_start'), $queryFilter->query('expiration_end')]);
      }
      if ($queryFilter->query('birth_start') && $queryFilter->query('birth_end')) {
        $search->orWhereBetween('birth_date', [$queryFilter->query('birth_start'), $queryFilter->query('birth_end')]);
      }
      if ($queryFilter->query('age_start') && $queryFilter->query('age_end')) {
        $birth_start = Carbon::today()->subYears($queryFilter->query('age_start'))->year.'-01-01-';
        $birth_end = Carbon::today()->subYears($queryFilter->query('age_end'))->year.'-01-01-';
        $search->orWhereBetween('birth_date', [$birth_end, $birth_start]);
      }
      if($isPDF) {
        $persons = $search->get();
          foreach ($persons as $key => $person) {
            if(count($person->relationship()->get())) {
              $relation = $person->relationship()->with('relationType')->first();
              $persons[$key]->relation = $relation->relationType->description;
            } else {
              $persons[$key]->relation = '';
            }
            if(count($person->shares()->get())) {
              $shareList = $this->parseShares($person->shares()->get());
              $persons[$key]->shareList = $shareList;
            } else {
              $persons[$key]->shareList = "";
            }
          }
        return $persons;
      }
      return $search->paginate($queryFilter->query('perPage'));
  }

        /**
     * seatch persons by query params
     * @param  object $queryFilter
    */
    public function searchToAssign($queryFilter) {
      $search;
      if($queryFilter->query('term') === null) {
        $search = $this->model->all();
      } else {
        $search = $this->model->select('id', 'name', 'last_name')->where('name', 'like', '%'.$queryFilter->query('term').'%')->get();
      }
     return $search;
    }

    /**
     * get persons by query params
     * @param  object $queryFilter
    */
    public function searchByCompany($queryFilter) {
      $search;
      if($queryFilter->query('term') === null) {
        $search = $this->model->all();
      } else {
        $search = $this->model->where('type_person', 2)->where('description', 'like', '%'.$queryFilter->query('term').'%')->get();
      }
     return $search;
    }

    /**
     * get persons by query params
     * @param  object $queryFilter
    */
    public function searchPersonsToAssign($queryFilter) {
      $search;
      if($queryFilter->query('term') === null) {
        $search = $this->model->query()->where('id', '!=', $queryFilter->query('id'))->where('type_person', 1)->paginate($queryFilter->query('perPage'));
      } else {
        $search = $this->model->where('id', '!=', $queryFilter->query('id'))->where('type_person', 1)->where('name', 'like', '%'.$queryFilter->query('term').'%')->paginate($queryFilter->query('perPage'));
      }
     return $search;
    }

        /**
     * get persons by query params
     * @param  object $queryFilter
    */
    public function searchCompanyPersonToAssign($queryFilter) {
      $search;
      if($queryFilter->query('term') === null) {
        $search = $this->model->query()->where('type_person', 2)->get();
      } else {
        $search = $this->model->where('id', '!=', $queryFilter->query('id'))->where('type_person', 2)->where('name', 'like', '%'.$queryFilter->query('term').'%')->get();
      }
     return $search;
    }

        /**
     * get persons by query params
     * @param  object $queryFilter
    */
    public function searchFamilyByPerson($queryFilter) {
      if($queryFilter->query('id') !== null) {
        return \DB::select("SELECT r.id, r.base_id, r.status, r.related_id   , p.name, p.last_name, p.rif_ci, p.card_number, r.relation_type_id,  t.description 
        FROM person_relations r, people p, relation_types t
        WHERE r.base_id=".$queryFilter->query('id')."
        AND r.related_id=p.id 
        AND t.id=r.relation_type_id");
       }
       return [];
    //   $search;
    //   if($queryFilter->query('term') === null) {
    //     $person = $this->model->query()->where('id', $queryFilter->query('id'))->with('family')->first();
    //     $familys = $person->family()->get();
    //     foreach ( $familys as $key => $family) {
    //       $currentPerson = PersonRelation::query()->where('base_id', $queryFilter->query('id'))->where('related_id', $family->id)->first();
    //       $relation = $this->relationTypeRepository->find($currentPerson->relation_type_id);
    //       $familys[$key]->relationType = $relation;
    //       $familys[$key]->id = $currentPerson->id;
    //       $familys[$key]->status = $currentPerson->status;
    //     }
    //    return $person->family = $familys;
    //   } else {
    //     $search = $this->model->where('id', '!=', 1)->where('type_person', 1)->where('name', 'like', '%'.$queryFilter->query('term').'%')->paginate($queryFilter->query('perPage'));
    //   }
    //  return $search;
    }

    public function assignPerson($attributes) {
      return $this->personRelationRepository->create($attributes);
    }

    public function getReportByPartner($id) {
        $person = $this->model->query()->where('id', $id)->with(['family', 'statusPerson', 'maritalStatus', 'gender', 'country','professions'])->first();
        $person['professionList'] = $this->parseProfessions($person->professions()->get());
        if($person->family()) {
          $familys = $person->family()->with(['statusPerson', 'maritalStatus', 'gender', 'country','professions'])->get();
          foreach ( $familys as $key => $family) {
            $professions = $this->parseProfessions($family->professions()->get());
            $currentPerson = PersonRelation::query()->where('base_id', $id)->where('related_id', $family->id)->first();
            $relation = $this->relationTypeRepository->find($currentPerson->relation_type_id);
            $familys[$key]->relationType = $relation;
            $familys[$key]->id = $currentPerson->id;
            $familys[$key]->professionList = $professions;
          }
          $person['familyMembers'] = $familys;
        }
        return $person;
    }

    public function parseProfessions($professions) {
      $str = '';
      $count = 0;
      foreach ( $professions as $profession) {
        $count = $count + 1;
        $coma = count($professions) == $count ? '' : ', ';
        $str .= $profession->description.''.$coma;
      }
      return $str;
    }

    public function parseShares($shares) {
      $str = '';
      $count = 0;
      foreach ( $shares as $share) {
        $count = $count + 1;
        $coma = count($shares) == $count ? '' : ', ';
        $str .= $share->share_number.''.$coma;
      }
      return $str;
    }

    public function getFamiliesPartnerByCard($card) {
      $cardNumber = $card;
      $person = $this->model->query()->select('id', 'isPartner')->where('card_number', $cardNumber)->first();
      if($person && $person->isPartner === 2) {
        $partner = $this->personRelationRepository->findPartner($person->id);
        $partner = $this->model->query()->select('id', 'card_number')->where('id', $partner->id)->first();
        $cardNumber = $partner->card_number;
      }
      $person = $this->model->query()->select('id', 'name', 'last_name', 'card_number', 'picture')
      ->where('isPartner', 1)
      ->where('card_number', $cardNumber)->with([
        'family' => function($query) {
          $query->with([
            'statusPerson',
            'relationship' => function($query){
              $query->select('id', 'related_id', 'base_id', 'relation_type_id')->with('relationType');
            },
          ]);
        },
        'statusPerson'
        ])->first();
      if($person) {
        $shares = $this->shareRepository->getListByPartner($person->id);
        if (count($shares)) {
          $person['shares'] = $shares;
        } else {
          $person['shares'] = null;
        }
        if($person->family()) {
          $familys = $person->family()->with([
            'statusPerson' => function($query) {
              $query->select('id', 'description');
            },
            'gender' => function($query) {
              $query->select('id', 'description');
            }])->get();
          foreach ( $familys as $key => $family) {
            $professions = $this->parseProfessions($family->professions()->get());
            $currentPerson = PersonRelation::query()->where('base_id', $person->id)->where('related_id', $family->id)->first();
            $relation = $this->relationTypeRepository->find($currentPerson->relation_type_id);
            $familys[$key]->relationType = $relation->description;
            $familys[$key]->id = $currentPerson->id;
            $familys[$key]->profilePicture = url('storage/partners/'.$family->picture);
            if($familys[$key]->card_number === $card ) {
              $familys[$key]->selectedFamily = true;
            } else {
              $familys[$key]->selectedFamily = false;
            }
          }
          $person['familyMembers'] = $familys;
          $person['picture'] =  url('storage/partners/'.$person['picture']);
        }
        return $person;
      }
      return $person;
  }

  public function getGuestByPartner($identification){
    $person = $this->model->query()->select('id','name','last_name', 'picture', 'primary_email', 'telephone1')->where('rif_ci', $identification)->first();
    if($person) {
      $person->picture = url('storage/partners/'.$person->picture);
      return $person;
    }
    return $person;
  }

  public function getLockersByLocation($request) {
    $lockerLocation = $request['location'];
    if($request['location'] == 0) {
      $lockerLocation = \DB::table('person_lockers')
      ->select('lockers.id', 'lockers.description', 'lockers.locker_location_id')
      ->join('lockers', 'lockers.id', '=', 'person_lockers.locker_id')
      ->join('people', 'people.id', '=', 'person_lockers.people_id')
      ->where('people.id', $request['id'])
      ->first();
      $lockerLocation = $lockerLocation->locker_location_id;
    }

   $data = \DB::table('person_lockers')
    ->select('lockers.id', 'lockers.description', 'lockers.locker_location_id')
    ->join('lockers', 'lockers.id', '=', 'person_lockers.locker_id')
    ->join('people', 'people.id', '=', 'person_lockers.people_id')
    ->where('people.id', $request['id'])
    ->where('lockers.locker_location_id', $lockerLocation)
    ->get();
    return $data;
  }

  public function getLockersByPartner($id){
    return $this->model->where('id', $id)->with([
      'lockers' => function($query) {
        $query->with(['location'])->orderBy('locker_location_id', 'asc');
      }
      ])->first();
  }

  public function getCountPersons(){
    $count = $this->model->whereIn('isPartner', [1, 2, 3])->count();
    $months = $this->accessControlRepository->getAllMonths();
    return array('count' => $count, 'months' => $months);
  }

  public function getCountPersonByIsPartner(int $isPartner){
    $count = $this->model->where('isPartner', $isPartner)->count();
    $months = $this->accessControlRepository->getMonthsByIsPartner($isPartner);
    return array('count' => $count, 'months' => $months);
  }

  public function getCountBirthdays(){
    $birth =  $this->model->whereIn('isPartner', [1, 2])->whereMonth('birth_date',date('m'))->count();
    return array('count' => $birth ? $birth : 0);
  }

  public function getFamilyByPartner($id) {
      $data = $this->model->where('id', $id)->with(['family'])->first();
      $array = array();
      $partner = $this->model->where('id', $id)->with(['relationship'])->first();
      $families = $data->family()->with([
        'relationship' => function($q) {
          $q->with([
            'relationType' => function($q) {
              $q->select('id', 'description');
            }
            ]);
        }
        ])->get();
      $partner->relation = 'SOCIO';
      array_push($array, $partner);
      foreach ($families as $key => $family) {
        $relation =  $family->relationship ? $family->relationship()->with(['relationType'])->first() : '';
        $relation =  $family->relationship ? $relation->relationType()->first()->description : '';
        $family->relation = $relation;
        array_push($array, $family);
      }
      return $array;
  }
  
}