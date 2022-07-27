<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
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

Route::group(["middleware" => "jwt.auth"] , function() {
    Route::get('/my-profile', [AuthController::class, 'myProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// games routes
Route::group(["middleware" => "jwt.auth"] , function() {
    Route::post('/new-game', [GameController::class, 'newGame']);
});

// channels routes
Route::group(["middleware" => "jwt.auth"] , function() {
    Route::post('/new-channel', [ChannelController::class, 'newChannel']);
    Route::get('/get-game-channels/{game_id}', [ChannelController::class, 'getChannelsByGameId']);
});

// users routes
Route::group(["middleware" => "jwt.auth"] , function() {
    Route::post('/join-to-channel', [UserController::class, 'joinToChannel']);
    Route::post('/leave-channel', [UserController::class, 'leaveChannel']);
});

// messages routes
Route::group(["middleware" => "jwt.auth"] , function() {
    Route::post('/post-message', [MessageController::class, 'postMessage']);
});