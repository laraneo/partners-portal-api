<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'description', 
        'created',
        'status',
        'people_id',
        'department_id',
    ];


    public function department()
    {
        return $this->hasOne('App\Department', 'id', 'department_id');
    }
}
