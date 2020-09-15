<?php

namespace App\Repositories;

use App\Share;
use App\Person;

class ShareRepository  {
  
    protected $post;

    public function __construct(Share $model, Person $personModel) {
      $this->model = $model;
      $this->personModel = $personModel;
    }

    public function all($perPage) {
      return $this->model->query()->select(
        'id', 
        'share_number', 
        'father_share_id', 
        'payment_method_id', 
        'id_persona', 
        'id_titular_persona',
        'id_factura_persona',
        'id_fiador_persona',
        'share_type_id'
        )->with([
          'fatherShare' => function($query){
          $query->select('id', 'share_number'); 
          }, 
          'partner' => function($query){
          $query->select('id', 'name', 'last_name'); 
          }, 
          'titular' => function($query){
          $query->select('id', 'name', 'last_name'); 
          },
          'paymentMethod' => function($query){
          $query->select('id', 'description'); 
          }, 
          'shareType' => function($query){
          $query->select('id', 'description', 'code'); 
          }, 
     ])->paginate($perPage);
    }

    public function filter($queryFilter, $isPDF = false) {
      $shares = $this->model->query()->select(
        'id', 
        'share_number',
        'status',
        'father_share_id', 
        'payment_method_id', 
        'id_persona', 
        'id_titular_persona',
        'id_factura_persona',
        'id_fiador_persona',
        'share_type_id'
        )->with([
          'shareMovements' => function($query) {
            $query->with([
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
              },
              'rateCurrency' => function($query){
                $query->select('id', 'description');
              },
              'saleCurrency' => function($query){
                $query->select('id', 'description');
              },
           ]);
          },
          'fatherShare' => function($query){
            $query->select('id', 'share_number'); 
          }, 
          'partner' => function($query){
            $query->select('id', 'name', 'last_name'); 
          }, 
          'titular' => function($query){
            $query->select('id', 'name', 'last_name'); 
          },
          'facturador' => function($query){
            $query->select('id', 'name', 'last_name'); 
          },
          'fiador' => function($query){
            $query->select('id', 'name', 'last_name'); 
          },
          'paymentMethod' => function($query){
            $query->select('id', 'description'); 
          }, 
          'shareType' => function($query){
            $query->select('id', 'description', 'code'); 
          }, 
      ]);
      if ($queryFilter->query('share')) {
        $shares->where('share_number', 'like', '%'.$queryFilter->query('share').'%');
      }
      if ($queryFilter->query('father_share')) {
        $shares->where('share_number', 'like', '%'.$queryFilter->query('father_share').'%')
        ->where('father_share_id',0);
      }
      if ($queryFilter->query('payment_method_id')) {
        $shares->where('payment_method_id', $queryFilter->query('payment_method_id'));
      }
      if ($queryFilter->query('share_type')) {
        $shares->where('share_type', $queryFilter->query('share_type'));
      }

      if ($queryFilter->query('persona')) {
          $persons = $this->personModel->query()->where('name','like', '%'.$queryFilter->query('persona').'%')->get();
          foreach ($persons as $key => $person) {
            $shares->where('id_persona', $person->id);
          }
      }

      if ($queryFilter->query('titular')) {
        $persons = $this->personModel->query()->where('name','like', '%'.$queryFilter->query('titular').'%')->get();
        foreach ($persons as $key => $person) {
          $shares->where('id_titular_persona', $person->id);
        }
      }

      if ($queryFilter->query('facturador')) {
        $persons = $this->personModel->query()->where('name','like', '%'.$queryFilter->query('facturador').'%')->get();
        foreach ($persons as $key => $person) {
          $shares->where('id_factura_persona', $person->id);
        }
      }

      if ($queryFilter->query('fiador')) {
        $persons = $this->personModel->query()->where('name','like', '%'.$queryFilter->query('fiador').'%')->get();
        foreach ($persons as $key => $person) {
          $shares->where('id_fiador_persona', $person->id);
        }
      }
      if ($isPDF) {
        return  $shares->get();
      }
      return $shares->paginate($queryFilter->query('perPage'));
    }


    public function find($id) {
      return $this->model->query()->where('id', $id)->with(['titular', 'facturador', 'fiador', 'tarjetaPrimaria', 'tarjetaSecundaria', 'tarjetaTerciaria' ])
      ->with(['tarjetaPrimaria' => function($query){
          $query->with(['bank','card']);
        }
      ])->with([ 'tarjetaSecundaria' => function($query){
        $query->with(['bank','card']);
        }
      ])->with([ 'tarjetaTerciaria' => function($query){
        $query->with(['bank','card']);
        }
      ])->first();
    }

    public function create($attributes) {
      return $this->model->create($attributes);
    }

    public function update($id, array $attributes) {
      return $this->model->find($id)->update($attributes);
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

    public function getByPartner($id) {
      return $this->model->query()->where('id_persona', $id)->with(['titular', 'facturador', 'fiador', 'paymentMethod'])->with([ 'tarjetaPrimaria' => function($query){
        $query->with(['bank','card']);
        }
        ])->with([ 'tarjetaSecundaria' => function($query){
          $query->with(['bank','card']);
          }
        ])->with([ 'tarjetaTerciaria' => function($query){
          $query->with(['bank','card']);
          }
        ])->get();
    }

            /**
     * get banks by query params
     * @param  object $queryFilter
    */
    public function searchToAssign($queryFilter) {
      $search;
      if($queryFilter->query('term') === null) {
        $search = $this->model->all();  
      } else {
        $search = $this->model->where('share_number', 'like', '%'.$queryFilter->query('term').'%')->get();
      }
     return $search;
    }

    public function getListByPartner($id) {
      return $this->model->query()->select('id', 'share_number')->where('id_persona', $id)->get();
    }

    public function findByShare($share) {
      return $this->model->where('share_number', $share)->with([
        'partner' => function($q) {
          $q->select('id', 'name', 'last_name', 'rif_ci', 'card_number', 'access_code');
        }
        ])->first();
    }
}