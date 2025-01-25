<?php

use App\Events\TestEvent;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AgentController,
    DashboardController,
    ConversationController,
    ReportsController
};

// Root Route with Authentication
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return auth()->user()->role === 'agent'
            ? redirect()->route('dashboard')
            : redirect()->route('dashboard');
    });

    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified') // Additional middleware if needed
        ->name('dashboard');

    // Agent Routes
    Route::resource('agents', AgentController::class);

    // Conversation Routes
    Route::resource('conversations', ConversationController::class);
    Route::get('/conversations/loadChat/{userId}', [ConversationController::class, 'loadChat'])
        ->name('conversations.loadChat');
    Route::post('/conversations/sendMessage', [ConversationController::class, 'sendMessages'])
        ->name('conversations.sendMessage');
    Route::post('/conversations/{id}/status', [ConversationController::class, 'updateStatus'])
        ->name('conversations.updateStatus');

    // Reports Route
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');

    // Agent Dashboard
    Route::get('/agent/dashboard', function () {
        return view('agent.dashboard'); // Customize the agent dashboard view
    })->name('agent.dashboard');
});


// Include Auth Routes (for Login, Register, etc.)
require __DIR__ . '/auth.php';
