<?php

namespace App\Repositories;

use App\Person;
use App\Share;
use App\AccessControl;

use Carbon\Carbon;

class AccessControlRepository  {
  
    protected $post;

    public function __construct(
      AccessControl $model, 
      Person $personModel,
      Share $shareModel
      ) {
      $this->model = $model;
      $this->personModel = $personModel;
      $this->shareModel = $shareModel;
    }

    public function find($id) {
      return $this->model->find($id, ['id', 'status', 'created', 'location_id', 'people_id', 'share_id']);
    }

    public function create($attributes) {
      return $this->model->create($attributes);
    }

    public function update($id, array $attributes) {
      return $this->model->find($id)->update($attributes);
    }
  
    public function all($perPage) {
      return $this->model->query()->select([
        'id', 
        'status', 
        'created', 
        'location_id', 
        'people_id', 
        'share_id'])->with([
          'location' => function($query){
            $query->select('id', 'description'); 
          },
          'share' => function($query){
            $query->select('id', 'share_number'); 
          }
        ])->paginate($perPage);
    }

    public function filter($queryFilter, $isPDF = false) {
      $data = $this->model->query()->select([
        'id', 
        'status', 
        'created', 
        'location_id', 
        'people_id',
        'guest_id',
        'share_id'])->with([
          'guest' => function($query){
            $query->select('id', 'name', 'last_name', 'rif_ci', 'primary_email', 'isPartner'); 
          },
          'person' => function($query){
            $query->select('id', 'name', 'last_name', 'rif_ci', 'card_number', 'isPartner'); 
          },
          'location' => function($query){
            $query->select('id', 'description'); 
          },
          'share' => function($query){
            $query->select('id', 'share_number'); 
          }
        ]);

        if ($queryFilter->query('share')) {
          $shares = $this->shareModel->query()->where('share_number','like', '%'.$queryFilter->query('share').'%')->get();
            foreach ($shares as $key => $share) {
              $data->where('share_id', $share->id);
            }
        }

        if ($queryFilter->query('partner_name')) {
          $persons = $this->personModel->query()->where('isPartner', 1)->where('name','like', '%'.$queryFilter->query('partner_name').'%')->get();
            foreach ($persons as $key => $person) {
              $data->where('people_id', $person->id);
            }
        }
  
        if ($queryFilter->query('partner_rif_ci')) {
          $persons = $this->personModel->query()->where('isPartner', 1)->where('rif_ci','like', '%'.$queryFilter->query('partner_rif_ci').'%')->get();
            foreach ($persons as $key => $person) {
              $data->where('people_id', $person->id);
            }
        }
  
        if ($queryFilter->query('partner_card_number')) {
          $persons = $this->personModel->query()->where('isPartner', 1)->where('card_number','like', '%'.$queryFilter->query('partner_card_number').'%')->get();
            foreach ($persons as $key => $person) {
              $data->where('people_id', $person->id);
            }
        }

        if ($queryFilter->query('guest_name')) {
          $persons = $this->personModel->query()->where('name','like', '%'.$queryFilter->query('guest_name').'%')->get();
          foreach ($persons as $key => $person) {
            $data->where('guest_id', $person->id);
          }
        }

        if ($queryFilter->query('guest_rif_ci')) {
          $persons = $this->personModel->query()->where('rif_ci','like', '%'.$queryFilter->query('guest_rif_ci').'%')->get();
          foreach ($persons as $key => $person) {
            $data->where('guest_id', $person->id);
          }
        }

        if ($queryFilter->query('location_id')) {
          $data->where('location_id', $queryFilter->query('location_id'));
        }

        if ($queryFilter->query('status')) {
          $data->where('status', $queryFilter->query('status'));
        }

        if ($queryFilter->query('created_start') && $queryFilter->query('created_end')) {
          $data->orWhereBetween('created', [$queryFilter->query('created_start'), $queryFilter->query('created_end')]);
        }

        if ($queryFilter->query('created_order')) {
          $data->orderBy('created', $queryFilter->query('created_order'));
        }

      if ($isPDF) {
        return  $data->get();
      }
      return $data->paginate($queryFilter->query('perPage'));
    }

    public function getList() {
      return $this->model->query()->select(['id', 'status', 'created', 'location_id', 'people_id', 'share_id'])->get();
    }

    public function delete($id) {
     return $this->model->find($id)->delete();
    }

    public function checkRecord($name)
    {
      $data = $this->model->where('description', $name)->first();
      if ($data) {
        return true;
      }
      return false; 
    }

        /**
     * get banks by query params
     * @param  object $queryFilter
    */
    public function search($queryFilter) {
      $search;
      if($queryFilter->query('term') === null) {
        $search = $this->model->all();  
      } else {
        $search = $this->model->where('description', 'like', '%'.$queryFilter->query('term').'%')->paginate($queryFilter->query('perPage'));
      }
     return $search;
    }

    public function getVisitsByMont($id) {
       return $this->model->where('status', 1)
       ->where('guest_id', $id)
       ->whereMonth('created', '=', date('m'))
       ->get();
    }

    public function getMonthsByIsPartner($isPartner) {
      $this->isPartner = $isPartner;
      if($isPartner === 3) {
        $lastMonth =  $this->model->where('status', 1)
        ->whereNotNull('guest_id')
        ->whereHas('guest' , function($q){
          $q->where('isPartner', $this->isPartner);
        })->whereMonth('created', '=', Carbon::now()->subMonth()->month)->count();
        $currentMonth = $this->model->where('status', 1)->whereNotNull('guest_id')->whereHas('guest' , function($q){
          $q->where('isPartner', $this->isPartner);
        })->whereMonth('created', '=', date('m'))->count();
      } else {
        $lastMonth =  $this->model->where('status', 1)->whereHas('person' , function($q){
          $q->where('isPartner', $this->isPartner);
        })->whereMonth('created', '=', Carbon::now()->subMonth()->month)->count();
        $currentMonth = $this->model->where('status', 1)->whereHas('person' , function($q){
          $q->where('isPartner', $this->isPartner);
        })->whereMonth('created', '=', date('m'))->count();
      }

      $lastMonth = $lastMonth ? $lastMonth : 0;
      $currentMonth = $currentMonth ? $currentMonth : 0;
      $data = $lastMonth.'/'.$currentMonth;
      return $data;
    }

    public function getAllMonths() {
      $lastMonth =  $this->model->where('status', 1)->whereMonth('created', '=', Carbon::now()->subMonth()->month)->count();
      $currentMonth = $this->model->where('status', 1)->whereMonth('created', '=', date('m'))->count();
      $lastMonth = $lastMonth ? $lastMonth : 0;
      $currentMonth = $currentMonth ? $currentMonth : 0;
      $data = $lastMonth.'/'.$currentMonth;
      return $data;
    }

    //   SELECT month(created) ,  count(*) as cant 
//   FROM [access_controls] c , people p
//   where guest_id=NULLL 
// and p.ispartner in (1,2)
// and p.people_id= c.people_id
//   and  status=1 and year(created)=year(getdate())  
// group by  month(created)
// order by month(created)
//      $first = $this->model->whereRaw('DATEDIFF("'.Carbon::today()->format('Y-m-d').'",expiration_date) <= 30')->count();
  public function getPartnersFamilyStatistics() {
    $data = $this->model->selectRaw('created ,year(created) year, monthname(created) month, count(*) data')
    ->where('status', 1)
    ->where('guest_id', NULL)
    ->whereHas('person' , function($q){
      $q->whereIn('isPartner', [1,2]);
    })->whereYear('created', '=', date('Y'))
    ->groupBy('year', 'month', 'created')
    ->orderBy('created', 'asc')
    ->get();
    return $data;
  }

  public function getGuestStatistics() {
    $data = $this->model->selectRaw('created ,year(created) year, monthname(created) month, count(*) data')
    ->where('status', 1)
    ->whereNotNull('guest_id')
    ->whereHas('guest' , function($q){
      $q->whereIn('isPartner', [3]);
    })->whereYear('created', '=', date('Y'))
    ->groupBy('year', 'month', 'created')
    ->orderBy('created', 'asc')
    ->get();
    return $data;
  }
}