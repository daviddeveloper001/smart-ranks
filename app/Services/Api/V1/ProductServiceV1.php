<?php

namespace App\Services\Api\V1;

use App\Models\Product;
use App\Exceptions\ProductException;
use App\Repositories\V1\ProductRepositoryV1;
use Illuminate\Http\Response;

class ProductServiceV1
{
    public function __construct(private ProductRepositoryV1 $productRepository) {}

    public function getAllProducts($filters, $perPage)
    {
        try {
            return Product::filter($filters)->paginate($perPage);
        } catch (\Exception $e) {
            throw new ProductException(
                'Failed to retrieve Products',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }

    public function getProductById(Product $product)
    {
        try {
            $result = $this->productRepository->find($product);
            if (!$result) {
                throw new ProductException('Product not found', Response::HTTP_NOT_FOUND);
            }
            return $result;
        } catch (\Exception $e) {
            throw new ProductException(
                'Failed to retrieve Product',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }

    public function createProduct(array $data)
    {
        try {
            return $this->productRepository->create($data);
        } catch (\Exception $e) {
            throw new ProductException(
                'Failed to create Product',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }

    public function updateProduct(Product $product, array $data)
    {
        try {
            return $this->productRepository->update($product, $data);
        } catch (\Exception $e) {
            throw new ProductException(
                'Failed to update Product',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }

    public function deleteProduct(Product $product)
    {
        try {
            return $this->productRepository->delete($product);
        } catch (\Exception $e) {
            throw new ProductException(
                'Failed to delete Product',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }
}