<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\ContactsController;


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

Route::post('login', [UsersController::class, 'authenticate'])->name('login');
Route::post('register', [UsersController::class, 'register']);



Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('logout', [UsersController::class, 'logout']);
   
    
    Route::get('contacts', [ContactsController::class, 'index']);
    Route::post('createcontact', [ContactsController::class, 'store']);
    Route::get('contact/{id}', [ContactsController::class, 'show']);
    Route::put('updatecontact/{contact}', [ContactsController::class, 'update']);
    Route::delete('deletecontact/{contact}', [ContactsController::class, 'destroy']);
    Route::post('restorecontact/{id}', [ContactsController::class, 'restore']); 

    Route::post('createservice', [ServicesController::class, 'store']);
    Route::get('services', [ServicesController::class, 'index']);
    Route::get('service/{id}', [ServicesController::class, 'show']);
    Route::put('updateservice/{service}', [ServicesController::class, 'update']);
    Route::delete('deleteservice/{service}', [ServicesController::class, 'destroy']);
    Route::post('restoreservice/{id}', [ServicesController::class, 'restore']);   

    Route::put('associate/{service}', [ServicesController::class, 'associate']);
    Route::put('freesupport/{id}', [ServicesController::class, 'freeSupport']);

    Route::get('services/support', [ServicesController::class, 'findByServiceSupport']);
    Route::get('services/unattendedarea', [ServicesController::class, 'findByUnattendedServiceAreaSupport']); 
    
    Route::get('services/client', [ServicesController::class, 'clientSearched']);   
    Route::get('services/numbersupport', [ServicesController::class, 'suportServiceSearched']);    
    Route::get('services/area', [ServicesController::class, 'areaSearched']);  
    Route::get('services/type', [ServicesController::class, 'typeServiceSearched']);    
    Route::get('services/unattended', [ServicesController::class, 'unattendedServiceSearched']);

});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
