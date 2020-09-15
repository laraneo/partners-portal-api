<?php

namespace App\Repositories;

use App\Widget;
use App\Role;
use App\WidgetRole;

class WidgetRepository  {
  
    protected $post;

    public function __construct(Widget $model) {
      $this->model = $model;
    }

    public function find($id) {
      return $this->model->query()->select([
        'id',
        'name',
        'slug',
        'description',
        'order',
        'show_mobile'
      ])->where('id', $id)->with(['roles'])->first();
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
        'name',
        'slug',
        'description',
        'order',
        'show_mobile'
    ])->paginate($perPage);
    }

    public function parseRoles($roles) {
      $string = '';
      $count = 0;
      foreach ($roles as $key => $value) {
        $role = Role::where('id', $value->role_id)->first();
        $count = $count + 1;
        if(count($roles) === $count) {
          $string .= $role->slug;

        } else {
          $string .= $role->slug.'|';
        }
      }
      return $string;
    }

    public function getList() {
        $widgets = $this->model->query()->select([
            'id',
            'name',
            'slug',
            'description',
            'order',
            'show_mobile'
        ])->with([
          'widgetRole' => function($q) {
            $q->with('role');
          }
          ])->get();
        $arrayWidgets = array();
        foreach ($widgets as $key => $value) {
            $widgetRoles = $value->widgetRole()->get();
            $widgetRoles = $this->parseRoles($widgetRoles);
            $existRole = auth()->user()->hasRole($widgetRoles); 
            if($existRole) {
                array_push($arrayWidgets, $value);
            }
        }
        $widgets = $arrayWidgets;
        return $widgets;
    }

    public function delete($id) {
      $data = $this->model->find($id);
      $data->widgetRole()->delete();
      $data->delete();
     return $data;
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
        $search = $this->model->where('description', 'like', '%'.$queryFilter->query('term').'%')->get();
      }
     return $search;
    }
}