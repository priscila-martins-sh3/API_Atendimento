<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controller\ServicesController;


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

Route::post('login', [UsersController::class, 'authenticate']);
Route::post('register', [UsersController::class, 'register']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('logout', [UsersController::class, 'logout']);
    Route::get('get_user', [UsersController::class, 'get_user']);
    Route::post('create', [ServicesController::class, 'store']);
    Route::get('services', [ServicesController::class, 'index']);
    Route::get('service/{id}', [ServicesController::class, 'show']);
    Route::put('update/{service}', [ServicesController::class, 'update']);

    Route::get('services/support', [ServicesController::class, 'findBySupportName']);
    Route::get('services/unattended', [ServicesController::class, 'findByUnattendedService']);   
   
});

Route::group(['middleware' => ['auth', 'gerente']], function () {    


});

Route::group(['middleware' => ['auth', 'gerente']], function () {    
    
    Route::get('', [ServicesController::class, 'clientSearched']);
    Route::get('', [ServicesController::class, 'servicesByClient' ]);
    Route::get('', [ServicesController::class, 'suportServicesSearched']);
    Route::get('', [ServicesController::class, 'servicesBySuport' ]);
    Route::get('', [ServicesController::class, 'areasSearched']);
    Route::get('', [ServicesController::class, 'servicesByAreas']);
    Route::get('', [ServicesController::class, 'typesServiceSearched']);
    Route::get('', [ServicesController::class, 'servicesByType']);
    Route::get('', [ServicesController::class, 'unattendedServiceSearched']);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
