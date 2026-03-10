<?php

use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ConversationController;
use App\Http\Controllers\Chat\MessageController;
use Illuminate\Support\Facades\Route;

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

Route::view('login', 'auth.login')->name('login');

Route::get('/auth/{provider}/redirect', [SocialController::class, 'redirect'])->name('social.redirect');
Route::match(['get', 'post'], '/auth/{provider}/callback', [SocialController::class, 'callback'])->name('social.callback');

Route::middleware('auth')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('chat.index');

    // Conversation
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversation.list');
    Route::post('/conversations', [ConversationController::class, 'store'])->name('conversation.store');
    Route::patch('/conversations/{conversation}/update', [ConversationController::class, 'update'])->name('conversation.update');
    Route::delete('/conversations/{conversation}/delete', [ConversationController::class, 'delete'])->name('conversation.delete');


    // Messages
    Route::get('/chat/messages/{conversation}/list', [MessageController::class, 'index'])->name('messages.list');
    Route::post('/chat/messages/{conversation}/send-text', [MessageController::class, 'sendText'])->name('messages.send-text');
    Route::post('/chat/message/{conversation}/send-audio', [MessageController::class, 'sendAudio'])->name('messages.send-audio');
});
