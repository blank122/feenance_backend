<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

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
    return $request->user();
});

Route::get('/apitest', function () {
    return "Hello World!";
});
Route::get('/dd', [ApiController::class, 'checktable']);

Route::post('/login', [ApiController::class, 'loginApi']);
Route::post('/saveGoogleInfo', [ApiController::class, 'storeGoogleInfoAPI']);
Route::get('/users', [ApiController::class, 'getUsers']);
// Route::get('/users/{id}', [ApiController::class, 'findUser']);
Route::get('/testResponse', [ApiController::class, 'testApi']);

Route::group(['middleware' => ['auth.sanctum.custom']], function () {

    // Route::get('/user/{id}', [ApiController::class, 'findUser']);
    // Route::post('/logout', [ApiController::class, 'logoutApi']);
    // // Route::get('/users', [ApiController::class, 'getUsers']);
    // Route::PUT('/updateUser/{id}', [ApiController::class, 'updateUserApi']);
    // Route::get('/expenses', [ApiController::class, 'expenses']);
    // Route::get('/weeklyExp', [ApiController::class, 'weeklyExpenses']);
    Route::get('/expenses', [ApiController::class, 'expenses']);
    Route::post('/createExp', [ApiController::class, 'createExpense']);
    Route::put('/updateExp/{id}', [ApiController::class, 'updateExpenses']);

    //link google account
    Route::post('/linkAccount', [ApiController::class, 'linkAccount']);


});