<?php

use App\Http\Controllers\AgentNotificationController;
use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MetadataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\ApiTokenMiddleware;
use App\Http\Controllers\PredefinedMessageController;

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

 Route::middleware([ApiTokenMiddleware::class])->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json(['message' => 'Authenticated']);
    });
});

Route::get('/chat-config', function () {
    return response()->json([
        'requireLogin' => env('CHAT_WIDGET_REQUIRE_LOGIN', true),  // Fetch from .env or config
    ]);
});

Route::post('/register', [AuthController::class, 'register']);



Route::post('/login', [AuthController::class, 'login']);

Route::get('/predefined-messages', [PredefinedMessageController::class, 'index']);
Route::post('/predefined-messages', [PredefinedMessageController::class, 'store']);
Route::delete('/predefined-messages/{id}', [PredefinedMessageController::class, 'destroy']);

Route::post('/notify-agent', [AgentNotificationController::class, 'notifyAgent']);
Route::post('/store-message', [MessageController::class, 'storeMessage']);
Route::post('/store-metadata', [MetadataController::class, 'store']);
Route::get('/metadata/{userId}', [MetadataController::class, 'getMetadata']);

