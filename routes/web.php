<?php

use App\Http\Controllers\Auth\SocialController;
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
