<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserControllerV1;
use App\Http\Controllers\Api\V1\ProductControllerV1;
use App\Http\Controllers\Api\V1\AuditLogControllerV1;
use App\Http\Controllers\Api\V1\CategoryControllerV1;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::apiResource('categories', CategoryControllerV1::class)->only('index', 'show')->except('store', 'update', 'destroy');

    Route::middleware(['auth:api','role:admin'])->group(function () {

        Route::apiResource('categories', CategoryControllerV1::class)->only('store', 'update', 'destroy')->except('index', 'show');
    });


    Route::apiResource('products', ProductControllerV1::class)->only(['index', 'show'])->except('store', 'update', 'destroy');

    Route::middleware(['auth:api','role:admin'])->group(function () {
        Route::apiResource('products', ProductControllerV1::class)->only('store', 'update', 'destroy')->except('index', 'show');
    });
});
