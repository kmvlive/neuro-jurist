@extends('layouts.app')

@section('title', 'Админ-панель — Нейро-юрист')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Админ-панель</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Управление пользователями и настройками системы</p>
    </div>

    <!-- Статистика -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Всего пользователей</dt>
                        <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['totalUsers'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Клиентов</dt>
                        <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['totalClients'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Администраторов</dt>
                        <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['totalAdmins'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Быстрые ссылки -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Управление</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.promo-codes.index') }}" class="block p-4 border-2 border-pink-500 rounded-lg hover:bg-pink-50 transition">
                <div class="font-semibold text-pink-600">🎁 Промокоды</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Скидки и акции</div>
            </a>
            <a href="{{ route('admin.quick-prompts.index') }}" class="block p-4 border-2 border-orange-500 rounded-lg hover:bg-orange-50 transition">
                <div class="font-semibold text-orange-600">📢 Реклама</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Quick-промпты и объявления</div>
            </a>
            <a href="{{ route('admin.settings.edit') }}" class="block p-4 border-2 border-gray-500 rounded-lg hover:bg-gray-50 dark:bg-gray-900 transition">
                <div class="font-semibold text-gray-600 dark:text-gray-400">⚙️ Настройки</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Счётчики и коды сайта</div>
            </a>
            <a href="{{ route('admin.stats') }}" class="block p-4 border-2 border-purple-500 rounded-lg hover:bg-purple-50 transition">
                <div class="font-semibold text-purple-600">📊 Статистика</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Трафик и бизнес-метрики</div>
            </a>
            <a href="{{ route('admin.revenue') }}" class="block p-4 border-2 border-green-500 rounded-lg hover:bg-green-50 transition">
                <div class="font-semibold text-green-600">📊 Выручка</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Отчёт по доходам и конверсии</div>
            </a>
            <a href="{{ route('admin.ai-usage.index') }}" class="block p-4 border-2 border-blue-500 rounded-lg hover:bg-blue-50 transition">
                <div class="font-semibold text-blue-600">🤖 AI-статистика</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Токены, скорость и стоимость</div>
            </a>
            <a href="{{ route('admin.users.index') }}" class="block p-4 border-2 border-primary rounded-lg hover:bg-blue-50 transition">
                <div class="font-semibold text-primary">Пользователи</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Управление пользователями системы</div>
            </a>
            <a href="{{ route('admin.plans.index') }}" class="block p-4 border-2 border-green-500 rounded-lg hover:bg-green-50 transition">
                <div class="font-semibold text-green-600">Тарифы</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Управление тарифами и ценами</div>
            </a>
            <a href="{{ route('admin.footer-links.index') }}" class="block p-4 border-2 border-orange-500 rounded-lg hover:bg-orange-50 transition">
                <div class="font-semibold text-orange-600">Меню футера</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Навигация в подвале сайта</div>
            </a>
        </div>
    </div>
</div>
@endsection
