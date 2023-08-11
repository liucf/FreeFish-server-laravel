<?php

use App\Actions\Fortify\UpdateUserPassword;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Models\Product;
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
Route::middleware('auth:sanctum')->put('/user', function (Request $request) {
    $request->user()->update($request->only('name')); 
    $request->user()->fresh();
    return new UserResource($request->user());
});
Route::post('update-password', [UpdateUserPassword::class , 'update'])->middleware('auth:sanctum');
Route::post('pay', [PaymentController::class , 'index']);
Route::middleware('auth:sanctum')->get('/orders', function (Request $request) {
    return $request->user()->orders->load('product');
});
Route::middleware('auth:sanctum')->get('/my-products', function (Request $request) {
    return Product::with('thumbs:name')->where('user_id' , $request->user()->id)->
        where('status', '<>' , 'sold')->paginate(12);
});

Route::middleware('auth:sanctum')->get('/my-sold', function (Request $request) {
    // return Product::with('thumbs:name')->where('user_id' , $request->user()->id)->
    //     where('status', 'sold')->paginate(12);
    return Order::with('product')->where('seller_id' , $request->user()->id)->latest()->paginate(12);
});

Route::middleware('auth:sanctum')->get('/order/byproduct/{id}', function (Request $request) {
    return Order::where('product_id' , $request->id)->first();
});

Route::get('product', [ProductController::class , 'index']);
Route::get('product/trending', [ProductController::class , 'trending']);
Route::get('product/show/{name}', [ProductController::class , 'show']);
Route::get('product/byid/{product}', [ProductController::class , 'showbyid']);
Route::get('product/related/{name}', [ProductController::class , 'related']);


Route::get('subcategory/featured', [SubcategoryController::class , 'featured']);
Route::get('subcategory/show/{name}', [SubcategoryController::class , 'show']);
Route::get('category', [CategoryController::class , 'index']);

Route::get('review/byuser/{id}', [ReviewController::class , 'byuser']);

Route::post('delivery', [OrderController::class , 'delivery']);
Route::post('confirm', [OrderController::class , 'confirm']);
Route::post('review', [OrderController::class , 'review']);