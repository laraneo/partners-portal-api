<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonRelation extends Model
{
    protected $fillable = [
        'base_id',
        'related_id',
        'relation_type_id',
        'status',
    ];

            /**
     * The sports that belong to the person.
     */
    public function relationType()
    {
        return $this->hasOne('App\RelationType', 'id', 'relation_type_id');
    }
}
