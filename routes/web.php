<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\ParserController;
use App\Http\Controllers\TransactionsController;
use App\Services\AI\AIChatService;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/home', [DashboardController::class, 'index'])->name('home');
    Route::get('/transactions', [DashboardController::class, 'transactions'])->name('transactions');
    Route::get('/import', [DashboardController::class, 'import'])->name('import');
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/parse', [ParserController::class, 'showForm'])->name('parse-form');
    Route::post('/parse', [ParserController::class, 'parse'])->name('parse');
    Route::post('/importTransaction', [ParserController::class, 'storeManual'])->name('importTransaction');
    Route::get('/goals', [DashboardController::class, 'goals'])->name('goals');
    Route::post('/goals/create', [GoalController::class, 'store'])->name('goals.store');
    Route::post('/goals/add-money', [GoalController::class, 'addMoney'])->name('goals.add-money');
    Route::post('/ask-ai', [AIChatController::class, 'sendMessage'])->name('ask-ai');
});

Route::get('/ai', function () {
    return view('test-ai');
});
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
