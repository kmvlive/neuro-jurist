<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AdminPlansController;
use App\Http\Controllers\Admin\AdminFooterLinksController;
use App\Http\Controllers\Admin\AdminStatsController;
use App\Http\Controllers\Admin\AiUsageController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminPromoCodesController;
use App\Http\Controllers\Admin\AdminQuickPromptsController;
use App\Http\Controllers\Admin\AdminPromptCategoriesController;
use App\Http\Controllers\Admin\AdminRevenueController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PromptCatalogController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\TBankWebhookController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/chat')->name('home');

// Тарифы и оплата

// Шаблоны документов
Route::get('/templates', [\App\Http\Controllers\TemplateController::class, 'index'])->name('templates.index');
Route::get('/templates/{key}', [\App\Http\Controllers\TemplateController::class, 'show'])->name('templates.show');
Route::post('/templates/{key}/generate', [\App\Http\Controllers\TemplateController::class, 'generate'])->name('templates.generate');

// Каталог промтов
Route::get("/prompts", [PromptCatalogController::class, "index"])->name("prompts.index");
Route::get("/prompts/{slug}", [PromptCatalogController::class, "show"])->name("prompts.show");

Route::get('/pricing', [PricingController::class, 'show'])->name('pricing');
Route::post('/pricing/{plan}', [PricingController::class, 'select'])->name('pricing.select');
Route::get('/payment/success', [TBankWebhookController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel', [TBankWebhookController::class, 'cancel'])->name('payment.cancel');
Route::post('/promo/check', [App\Http\Controllers\PromoCheckController::class, 'check'])->name('promo.check');
Route::post('/tbank/webhook', [TBankWebhookController::class, 'handle'])->name('tbank.webhook');

// Чат
Route::get('/chat', [ChatController::class, 'show'])->name('chat.show');
Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
Route::get('/chat/history', [ChatController::class, 'getChats'])->name('chat.history');
Route::get('/chat/{id}/messages', [ChatController::class, 'getChatMessages'])->name('chat.messages');
Route::get('/chat/search-messages', [ChatController::class, 'searchMessages'])->name('chat.search');
Route::post('/chat/stream', [ChatController::class, 'stream'])->name('chat.stream');
Route::post('/chat/create', [ChatController::class, 'create'])->name('chat.create');
Route::delete('/chat/{id}', [ChatController::class, 'destroy'])->name('chat.destroy');

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

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/list', [ChatController::class, 'getChats'])->name('list');
    });
});

Route::prefix('admin')->name('admin.')->middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', AdminUsersController::class);
    Route::resource("plans", AdminPlansController::class)->except(["create", "show"]);
    Route::resource('footer-links', AdminFooterLinksController::class)->except(['create', 'show']);
    Route::get('stats', [AdminStatsController::class, 'index'])->name('stats');
    Route::get('revenue', [AdminRevenueController::class, 'index'])->name('revenue');
    Route::get('settings', [AdminSettingsController::class, 'edit'])->name('settings.edit');
    Route::post('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::get('ai-usage', [AiUsageController::class, 'index'])->name('ai-usage.index');
    Route::resource('promo-codes', AdminPromoCodesController::class);
    Route::resource('quick-prompts', AdminQuickPromptsController::class);
    Route::get('quick-prompts/{quickPrompt}/ad', [AdminQuickPromptsController::class, 'editAd'])->name('quick-prompts.ad.edit');
    Route::put('quick-prompts/{quickPrompt}/ad', [AdminQuickPromptsController::class, 'updateAd'])->name('quick-prompts.ad.update');
    Route::post('quick-prompts/toggle-all-ads', [AdminQuickPromptsController::class, 'toggleAllAds'])->name('quick-prompts.toggle-all-ads');
    Route::resource("prompt-categories", AdminPromptCategoriesController::class);
});
