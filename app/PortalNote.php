<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PortalNote extends Model
{
    protected $fillable = [
        'id', 
        'description',
        'portal_department_id',
        'portal_note_type_id',
        'usuario',
        'share_number',
        'fecha',
        'attach1',
        'attach2',
        'status',
    ];
}
