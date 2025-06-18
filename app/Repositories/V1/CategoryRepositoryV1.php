<?php

namespace App\Repositories\V1;

use App\Models\Category;
use App\Repositories\V1\BaseRepositoryV1;

class CategoryRepositoryV1 extends BaseRepositoryV1
{
    const RELATIONS = [];

    public function __construct(Category $category)
    {
        parent::__construct($category, self::RELATIONS);
    }
}