<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/property-types', [PropertyTypeController::class, 'index']);
Route::get('/property-types/{id}', [PropertyTypeController::class, 'show']);
Route::get('/property-types/{id}/properties', [PropertyTypeController::class, 'getPropertiesByType']);

Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/search', [PropertyController::class, 'search']);
Route::get('/properties/{id}', [PropertyController::class, 'show']);


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::resource('property-types', PropertyTypeController::class)
        ->only(['store', 'update', 'destroy']);
    Route::resource('properties', PropertyController::class)
        ->only(['store', 'update', 'destroy']);
    Route::resource('inquiries', InquiryController::class)
        ->except(['create', 'edit', 'update']);

    Route::get('/inquiries/{id}/users', [InquiryController::class, 'getUserInquiries']);
    Route::post('/logout', [AuthController::class, 'logout']);
});