<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MenuItemIcon extends Model
{
    protected $fillable = [
        'name', 
        'slug', 
        'description',
        'import',
    ];
}
