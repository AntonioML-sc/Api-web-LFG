<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
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

// presentation
Route::get('/', function() { return "api root"; });

// provisional. add middleware superadmin
Route::post('/roles', [RoleController::class, 'newRole']);

// authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);