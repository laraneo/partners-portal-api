<?php

namespace App\Repositories;

use App\Record;

use Carbon\Carbon;

class RecordRepository  {
  
    protected $post;

    public function __construct(Record $model) {
      $this->model = $model;
    }

    public function find($id) {
      return $this->model->find($id, [
        'description',
        'created',
        'days',
        'blocked',
        'expiration_date',
        'file1',
        'file2',
        'file3',
        'file4',
        'file5',
        'record_type_id',
        'people_id',
    ]);
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
        'description',
        'created',
        'days',
        'blocked',
        'expiration_date',
        'file1',
        'file2',
        'file3',
        'file4',
        'file5',
        'record_type_id',
        'people_id'
    ])->with([
        'type' => function($query) {
            $query->select(['id', 'description']);
        },
        ])->paginate($perPage);
    }

    public function getList() {
      return $this->model->query()->select([
        'id',
        'description',
        'created',
        'days',
        'blocked',
        'expiration_date',
        'file1',
        'file2',
        'file3',
        'file4',
        'file5',
        'record_type_id',
        'people_id'
    ])->get();
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

    public function getByPerson($queryFilter) {
      return $this->model->query()->select([
        'id',
        'description',
        'created',
        'days',
        'blocked',
        'expiration_date',
        'file1',
        'file2',
        'file3',
        'file4',
        'file5',
        'record_type_id',
        'people_id'
    ])->with([
        'type' => function($query) {
            $query->select(['id', 'description']);
        },
        ])->where('people_id', $queryFilter->query('id'))->paginate($queryFilter->query('perPage'));
    }

    public function getBlockedRecord($id){
      return $this->model->query()->select([
        'id',
        'description',
        'created',
        'days',
        'blocked',
        'expiration_date',
        'record_type_id',
        'people_id'
    ])->where('blocked', 1)->where('people_id', $id)->get();
    }

    public function getRecordsStatistics() {
      $blockedYes =  $this->model->where('blocked', 1)->whereMonth('expiration_date', '=', Carbon::now())->count();
      $blockedNo = $this->model->where('blocked', 0)->count();
      $blockedYes = $blockedYes ? $blockedYes : 0;
      $blockedNo = $blockedNo ? $blockedNo : 0;
      $data = $blockedYes.'/'.$blockedNo;
      return array('blockeds' => $data);
    }
}