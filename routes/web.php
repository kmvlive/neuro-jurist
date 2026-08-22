<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PricingController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Публичные маршруты
Route::get('/', [HomeController::class, 'index'])->name('home');

// Страница тарифов (доступна без авторизации)
Route::get('/pricing', [PricingController::class, 'show'])->name('pricing');
Route::post('/pricing/{plan}', [PricingController::class, 'select'])->name('pricing.select');

// Чат доступен БЕЗ авторизации (гостевой режим)
Route::get('/chat', [ChatController::class, 'show'])->name('chat.show');
Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
Route::get('/chat/history', [ChatController::class, 'getChats'])->name('chat.history');
Route::get('/chat/{id}/messages', [ChatController::class, 'getChatMessages'])->name('chat.messages');
Route::post('/chat/create', [ChatController::class, 'create'])->name('chat.create');
Route::delete('/chat/{id}', [ChatController::class, 'destroy'])->name('chat.destroy');

// Маршруты аутентификации
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
    
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Маршруты для авторизованных пользователей
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Личный кабинет клиента
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Чаты (дополнительные маршруты для авторизованных)
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/list', [ChatController::class, 'getChats'])->name('list');
    });
});

// Админ-панель
Route::prefix('admin')->name('admin.')->middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Управление пользователями
    Route::resource('users', AdminUsersController::class);
});
