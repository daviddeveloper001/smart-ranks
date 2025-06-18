<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Filters\ProductFilter;
use App\Services\Api\V1\ProductServiceV1;
use App\Http\Controllers\Api\V1\ApiControllerV1;
use App\Http\Resources\Api\V1\Product\ProductResourceV1;
use App\Http\Requests\Api\V1\Product\StoreProductRequestV1;
use App\Http\Requests\Api\V1\Product\UpdateProductRequestV1;

class ProductControllerV1 extends ApiControllerV1
{
    public function __construct(private ProductServiceV1 $productService) {}

    public function index(ProductFilter $filters)
    {
        try {
            $perPage = request()->input('per_page', 10);
            $products = $this->productService->getAllProducts($filters, $perPage);

            return $this->ok('Products retrieved successfully', ProductResourceV1::collection($products));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function store(StoreProductRequestV1 $request)
    {
        try {
            $product = $this->productService->createProduct($request->validated());
            return $this->ok('Product created successfully', new ProductResourceV1($product));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function show(Product $product)
    {
        try {
            return $this->ok('Product retrieved successfully', new ProductResourceV1($product));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function update(UpdateProductRequestV1 $request, Product $product)
    {
        try {
            $product = $this->productService->updateProduct($product, $request->validated());
            return $this->ok('Product updated successfully', new ProductResourceV1($product));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(Product $product)
    {
        try {
            $this->productService->deleteProduct($product);
            return $this->ok('Product deleted successfully');
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }
}