<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return new UserResource($request->user());
});


Route::get('product', [ProductController::class , 'index']);
Route::get('product/trending', [ProductController::class , 'trending']);
Route::get('product/show/{name}', [ProductController::class , 'show']);
Route::get('product/related/{name}', [ProductController::class , 'related']);


Route::get('subcategory/featured', [SubcategoryController::class , 'featured']);
Route::get('subcategory/show/{name}', [SubcategoryController::class , 'show']);
Route::get('category', [CategoryController::class , 'index']);

Route::get('review/byuser/{id}', [ReviewController::class , 'byuser']);

Route::post('pay', [PaymentController::class , 'index']);

