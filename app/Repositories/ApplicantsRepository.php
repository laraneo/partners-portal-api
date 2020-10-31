<?php

namespace App\Repositories;

use App\Applicants;
use App\Parameter;

use Carbon\Carbon;

class ApplicantsRepository  {
  
    protected $post;

    public function __construct(
        Applicants $model,
        Parameter $parameterModel
        ) {
      $this->model = $model;
      $this->parameterModel = $parameterModel;
    }

    public function find($id) {
      return $this->model->find($id);
    }

    public function create($attributes) {
      return $this->model->create($attributes);
    }

    public function update($id, array $attributes) {
      return $this->model->find($id)->update($attributes);
    }
  
    public function all($queryFilter) {
      $searchQuery = trim($queryFilter->query('term'));
      $requestData = ['sCI', 'sNombres', 'sApellidos'];
      $data = $this->model->query()->where(function($q) use($searchQuery, $requestData, $queryFilter) {
        if ($queryFilter->query('term') !== NULL) {
            foreach ($requestData as $field) {
                $q->orWhere($field, 'like', "%{$searchQuery}%");
            }
         }

         if ($queryFilter->query('createdStart') !== NULL && $queryFilter->query('createdEnd') !== NULL) {
            $q->whereBetween('dCreated', [$queryFilter->query('createdStart'), $queryFilter->query('createdEnd')]);
          }
  

         if ($queryFilter->query('nStatus') !== NULL) {
            $q->where('nStatus', $queryFilter->query('nStatus'));
          }

      })->orderBy('id', 'DESC')->paginate($queryFilter->query('perPage'));
      foreach ($data as $key => $value) {
        if($value->sArchivo !== null) {
          $data[$key]->sArchivo = url('storage/applicants/'.$value->sArchivo);
        } else {
          $value->sArchivo = null;
        }
      }
      return $data;
    }

    public function getList() {
      return $this->model->query()->get();
    }

    public function delete($id) {
     return $this->model->find($id)->delete();
    }

    public function checkRecord($name)
    {
      $data = $this->model->where('sCI', $name)->first();
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

    public function getCurrentApplicants() {
        $dayParameter = $this->parameterModel->query()->where('parameter', 'APPLICANT_EXPIRATIONDAYS')->first();
        $applicants = $this->model->query()->where('nStatus',1)->get();


        $applicantsArray = array();
        foreach ($applicants as $key => $value) {
            $daysExpiration = Carbon::parse($value->dCreated)->addDays($dayParameter->value);
            if($daysExpiration > Carbon::now()) {
                if($value->picture !== null) {
                    $applicants[$key]->picture = url('storage/applicants/'.$value->picture);
                } else {
                    $value->picture = null;
                }
                if($value->sArchivo !== null) {
                    $applicants[$key]->sArchivo = url('storage/applicants/'.$value->sArchivo);
                } else {
                    $value->sArchivo = null;
                }
                array_push($applicantsArray, $value);
            }
        }
        return $applicantsArray;
       }
   
}