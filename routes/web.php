<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CrawlerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

Route::post('/sanctum/token', function (Request $request) {

    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'The provided credentials are incorrect.',
        ], 401);
        // throw ValidationException::withMessages([
        //     'email' => ['The provided credentials are incorrect.'],
        // ]);
    }

    return $user->createToken($request->device_name)->plainTextToken;
});


Route::get('/', function () {
    return view('welcome');
});

Route::get('crawler/category', [CrawlerController::class, 'category']);

Route::get('crawler/computer', [CrawlerController::class, 'computer']);

Route::post('test', function () {
    $fillInfo = request()->input('fillInfo');
    dd($fillInfo);
});
