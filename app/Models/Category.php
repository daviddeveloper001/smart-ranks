<?php

namespace App\Models;

class Category extends ModelBase
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        
    ];
}