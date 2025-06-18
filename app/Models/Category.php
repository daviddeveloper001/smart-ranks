<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends ModelBase
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    //Getter and Setter

    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }

}