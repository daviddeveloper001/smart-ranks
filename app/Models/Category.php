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
}