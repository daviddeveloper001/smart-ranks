<?php

namespace App\Services\Api\V1;

use App\Models\Category;
use App\Exceptions\CategoryException;
use App\Repositories\V1\CategoryRepositoryV1;
use Illuminate\Http\Response;

class CategoryServiceV1
{
    public function __construct(private CategoryRepositoryV1 $categoryRepository) {}

    public function getAllCategorys($filters, $perPage)
    {
        try {
            return Category::filter($filters)->paginate($perPage);
        } catch (\Exception $e) {
            throw new CategoryException(
                'Failed to retrieve Categorys',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }

    public function getCategoryById(Category $category)
    {
        try {
            $result = $this->categoryRepository->find($category);
            if (!$result) {
                throw new CategoryException('Category not found', Response::HTTP_NOT_FOUND);
            }
            return $result;
        } catch (\Exception $e) {
            throw new CategoryException(
                'Failed to retrieve Category',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }

    public function createCategory(array $data)
    {
        try {
            return $this->categoryRepository->create($data);
        } catch (\Exception $e) {
            throw new CategoryException(
                'Failed to create Category',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }

    public function updateCategory(Category $category, array $data)
    {
        try {
            return $this->categoryRepository->update($category, $data);
        } catch (\Exception $e) {
            throw new CategoryException(
                'Failed to update Category',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }

    public function deleteCategory(Category $category)
    {
        try {
            return $this->categoryRepository->delete($category);
        } catch (\Exception $e) {
            throw new CategoryException(
                'Failed to delete Category',
                developerHint: $e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                previous: $e
            );
        }
    }
}