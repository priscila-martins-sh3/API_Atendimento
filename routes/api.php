<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controller\ServicesController;
use App\Http\Controller\ContactsController;


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
    Route::post('createcontact', [Contactsontroller::class, 'store']);
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


    Route::get('services/support', [ServicesController::class, 'findBySupportName']);
    Route::get('services/unattended', [ServicesController::class, 'findByUnattendedService']); 
    
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
