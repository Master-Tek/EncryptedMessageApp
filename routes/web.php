<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ConversationsController;
use Illuminate\Support\Facades\Redirect;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return Redirect::to('/login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route to retrieve messages for the current user
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages/create', [MessageController::class, 'createMessage']);
    Route::post('/messages/markAsRead', [MessageController::class, 'markAsRead']);
    Route::delete('/messages/{id}', [MessageController::class, 'softDelete'])->name('messages.softDelete');
    
    Route::get('/conversations', [ConversationsController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/new',[ConversationsController::class, 'new'])->name('conversations.new');
    Route::get('/conversations/{id}', [ConversationsController::class, 'show'])->name('conversations.show');
    Route::get('/conversations/{id}/conversation-exists',[ConversationsController::class, 'exists'])->name('conversations.exists');
});

require __DIR__.'/auth.php';
