<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PortalNoteType extends Model
{
    protected $fillable = [
        'description', 
        'portal_department_id',
    ];
}
