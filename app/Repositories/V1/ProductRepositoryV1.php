<?php

namespace App\Repositories\V1;

use App\Models\Product;
use App\Repositories\V1\BaseRepositoryV1;

class ProductRepositoryV1 extends BaseRepositoryV1
{
    const RELATIONS = [];

    public function __construct(Product $product)
    {
        parent::__construct($product, self::RELATIONS);
    }
}