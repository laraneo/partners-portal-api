<?php

namespace App\Repositories;

use App\ShareMovement;

class ShareMovementRepository  {
  
    protected $post;

    public function __construct(ShareMovement $model) {
      $this->model = $model;
    }

    public function find($id) {
      return $this->model->find($id, ['id', 'description', 'rate']);
    }

    public function create($attributes) {
      return $this->model->create($attributes);
    }

    public function update($id, array $attributes) {
      return $this->model->find($id)->update($attributes);
    }
    
    public function all($perPage) {
      return $this->model->query()->with([
        'share' => function($query){
            $query->select('id', 'share_number'); 
        }, 
        'transaction' => function($query){
            $query->select('id', 'description');
        }, 
        'partner' => function($query){
            $query->select('id', 'name', 'last_name');
        },
        'titular' => function($query){
          $query->select('id', 'name', 'last_name');
      }
     ])->paginate($perPage);
    }

    public function getList() {
      return $this->model->query()->with([
        'share' => function($query){
            $query->select('id', 'share_number'); 
        }, 
        'transaction' => function($query){
            $query->select('id', 'description');
        }, 
        'partner' => function($query){
            $query->select('id', 'name', 'last_name');
        },
        'titular' => function($query){
          $query->select('id', 'name', 'last_name');
      }
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

    public function getLastMovement($share) {
      return $this->model->where('share_id ', $share)->with(['transaction'])->orderBy('created', 'desc')->first();
    }
}