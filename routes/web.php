<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
Route::get('users', function() {
	return 'Funcionando!!!';
});
*/

Route::get('/', function () {
    return view('welcome');
});

//Route::get('users', [UsersController::class, 'index']);
//Route::post('users', [UsersController::class, 'store']);
//Route::post('/cadastro', [UsersController::class, 'cadastro']);