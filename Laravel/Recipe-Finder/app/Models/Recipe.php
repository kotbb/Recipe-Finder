<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'ingredients',
        'instructions',
        'image_path',
    ];
}