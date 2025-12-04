<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GigaChatController;
use App\Http\Controllers\ParserController;
use App\Services\AI\GigaChatService;
use Illuminate\Support\Facades\Route;

Route::get('/home', function () {
    return view('home');
})->name('home');
Route::middleware('auth')->group(function () {
    Route::get('/parse', [ParserController::class, 'showForm'])->name('parse-form');
    Route::post('/parse', [ParserController::class, 'parse'])->name('parse');

    Route::post('/ask-ai', [GigaChatController::class, 'sendMessage'])->name('ask-ai');
});
Route::get('/login',[AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
