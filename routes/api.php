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
    Route::put('/my-profile/update', [AuthController::class, 'updateMyProfile']);
    Route::delete('/my-profile/delete', [AuthController::class, 'deleteMyProfile']);
});

// games routes
Route::get('/games', [GameController::class, 'getGames']);

Route::group(["middleware" => ["jwt.auth", "isAdmin"]] , function() {
    Route::post('games/add-game', [GameController::class, 'newGame']);
    Route::put('/games/update-game/{gameId}', [GameController::class, 'updateGame']);
    Route::delete('/games/delete-game/{gameId}', [GameController::class, 'deleteGame']);
});

// channels routes
Route::group(["middleware" => "jwt.auth"] , function() {
    Route::post('/channels/new-channel', [ChannelController::class, 'newChannel']);
    Route::get('/channels/get-by-game/{game_id}', [ChannelController::class, 'getChannelsByGameId']);
    Route::put('/channels/update/{channelId}', [ChannelController::class, 'updateChannel']);
    Route::delete('/channels/delete/{channelId}', [ChannelController::class, 'deleteChannel']);
});

// users routes
Route::group(["middleware" => "jwt.auth"] , function() {
    Route::post('/join-to-channel', [UserController::class, 'joinToChannel']);
    Route::post('/leave-channel', [UserController::class, 'leaveChannel']);
});

Route::group(["middleware" => ["jwt.auth", "isSuperAdmin"]] , function() {
    Route::post('/user/set-role/{newRole}/{id}', [UserController::class, 'setUserRole']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::get('/users/by-id/{userId}', [UserController::class, 'getUserById']);
});

// messages routes
Route::group(["middleware" => "jwt.auth"] , function() {
    Route::post('/messages/post', [MessageController::class, 'postMessage']);
    Route::get('/messages/get-by-channel/{channel_id}', [MessageController::class, 'getMessagesByChannelId']);
    Route::put('/messages/edit/{option}/{msgId}', [MessageController::class, 'editMessage']);
});