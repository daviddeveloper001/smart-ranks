<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use Illuminate\Http\Response;
use App\Filters\CategoryFilter;
use App\Services\Api\V1\CategoryServiceV1;
use App\Http\Controllers\Api\V1\ApiControllerV1;
use App\Http\Resources\Api\V1\Category\CategoryResourceV1;
use App\Http\Requests\Api\V1\Category\StoreCategoryRequestV1;
use App\Http\Requests\Api\V1\Category\UpdateCategoryRequestV1;

class CategoryControllerV1 extends ApiControllerV1
{
    public function __construct(private CategoryServiceV1 $categoryService) {}

    public function index(CategoryFilter $filters)
    {
        try {
            $perPage = request()->input('per_page', 10);
            $categories = $this->categoryService->getAllCategorys($filters, $perPage);
            if ($this->include('products')) {
                $categories->load('products');
            }

            return $this->ok('Categories retrieved successfully', CategoryResourceV1::collection($categories));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function store(StoreCategoryRequestV1 $request)
    {
        try {
            $category = $this->categoryService->createCategory($request->validated());
            return $this->ok('Category created successfully', new CategoryResourceV1($category));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function show(Category $category)
    {
        try {
            if ($this->include('products')) {
                $category->load('products');
            }
            return $this->ok('Category retrieved successfully', new CategoryResourceV1($category));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function update(UpdateCategoryRequestV1 $request, Category $category)
    {
        try {
            $category = $this->categoryService->updateCategory($category, $request->validated());
            return $this->ok('Category updated successfully', new CategoryResourceV1($category));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(Category $category)
    {
        try {
            if (!auth()->user()->hasRole('admin')) {
                return $this->error('You do not have permission to delete this category', Response::HTTP_FORBIDDEN);
            }
            $this->categoryService->deleteCategory($category);
            return $this->ok('Category deleted successfully');
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }
}
