<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\UserController;

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

//Login
Route::get('/', [LoginController::class, 'login']);
Route::get('/logout', [LoginController::class, 'logout']);
Route::post('/validate', [LoginController::class, 'validateUser']);

//Admin
Route::get('home', [MainController::class, 'home']);

//User
Route::post('user/register', [UserController::class, 'register']);
Route::get('user/reset/{usr_uuid}', [UserController::class, 'reset']);
Route::post('user/change-password', [UserController::class, 'updatePassword']);
Route::post('user/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('user/update', [UserController::class, 'update']);
Route::post('user/update-password', [UserController::class, 'updatePassword2']);
Route::get('user/list/active', [UserController::class, 'active']);
Route::get('user/list/inactive', [UserController::class, 'inactive']);
Route::get('user/list/activate/{usr_uuid}', [UserController::class, 'activate']);
Route::get('user/list/deactivate/{usr_uuid}', [UserController::class, 'deactivate']);
Route::get('user/list/add-admin/{usr_uuid}', [UserController::class, 'addAdmin']);
Route::get('user/list/remove-admin/{usr_uuid}', [UserController::class, 'removeAdmin']);

//Announcements
Route::post('announcement/save', [AnnouncementController::class, 'save']);
Route::get('announcement/delete/{ann_uuid}', [AnnouncementController::class, 'delete']);