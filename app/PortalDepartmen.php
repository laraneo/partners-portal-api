<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PortalDepartmen extends Model
{
    protected $fillable = [
        'description', 
        'telephone',
        'email',
    ];
}
