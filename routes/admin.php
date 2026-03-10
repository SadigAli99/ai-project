<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminConversationController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

// Auth (no admin middleware)
Route::withoutMiddleware('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
});

Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

Route::get('/', [AdminController::class, 'index'])->name('dashboard');
Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
Route::get('/conversations', [AdminConversationController::class, 'index'])->name('conversations.index');
Route::get('/conversations/{conversation}', [AdminConversationController::class, 'show'])->name('conversations.show');
