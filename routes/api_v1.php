<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserControllerV1;
use App\Http\Controllers\Api\V1\ProductControllerV1;
use App\Http\Controllers\Api\V1\AuditLogControllerV1;
use App\Http\Controllers\Api\V1\CategoryControllerV1;

Route::middleware('auth:sanctum')->apiResource('categories', CategoryControllerV1::class);
Route::middleware('auth:sanctum')->apiResource('products', ProductControllerV1::class);
Route::middleware('auth:sanctum')->apiResource('audit-logs', AuditLogControllerV1::class);
Route::middleware('auth:sanctum')->apiResource('users', UserControllerV1::class);
