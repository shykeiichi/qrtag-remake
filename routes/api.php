<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\UsersController;
use App\Http\Middleware\VerifySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/auth/login', [AuthController::class, 'login']);
Route::any('/auth/logout', [AuthController::class, 'logout']);

Route::post('/events', [EventsController::class, 'store'])->middleware(VerifySession::class);
Route::post('/events/join', [EventsController::class, 'join'])->middleware(VerifySession::class);
Route::post('/events/leave', [EventsController::class, 'leave'])->middleware(VerifySession::class);
Route::delete('/events/{eventId}', [EventsController::class, 'delete'])->where('eventId', '[0-9]+')->middleware(VerifySession::class);
Route::patch('/events/{eventId}', [EventsController::class, 'update'])->where('eventId', '[0-9]+')->middleware(VerifySession::class);
Route::post('/events/{eventId}/targets', [EventsController::class, 'giveTargets'])->where('eventId', '[0-9]+')->middleware(VerifySession::class);
Route::post('/events/{eventId}/players', [EventsController::class, 'addPlayer'])->where('eventId', '[0-9]+')->middleware(VerifySession::class);
Route::patch('/events/{eventId}/change-target', [EventsController::class, 'changeTarget'])->where('eventId', '[0-9]+')->middleware(VerifySession::class);
Route::patch('/events/{eventId}/revive', [EventsController::class, 'reviveUser'])->where('eventId', '[0-9]+')->middleware(VerifySession::class);
Route::patch('/events/{eventId}/kill', [EventsController::class, 'killUser'])->where('eventId', '[0-9]+')->middleware(VerifySession::class);
Route::patch('/events/{eventId}/revive-all', [EventsController::class, 'reviveAll'])->where('eventId', '[0-9]+')->middleware(VerifySession::class);

Route::post('/users', [UsersController::class, 'store'])->middleware(VerifySession::class);
Route::post('/users/{userId}', [UsersController::class, 'updateUser'])->where('userId', '[0-9]+')->middleware(VerifySession::class);
Route::any('/users/tag', [UsersController::class, 'tag'])->middleware(VerifySession::class);
Route::get('/users/{userId}/alive', [UsersController::class, 'alive']);