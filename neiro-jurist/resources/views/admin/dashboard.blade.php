@extends('layouts.app')

@section('title', 'Админ-панель — Нейро-юрист')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Админ-панель</h1>
        <p class="text-gray-600 mt-2">Управление пользователями и настройками системы</p>
    </div>

    <!-- Статистика -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Всего пользователей</dt>
                        <dd class="text-2xl font-semibold text-gray-900">{{ $stats['totalUsers'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Клиентов</dt>
                        <dd class="text-2xl font-semibold text-gray-900">{{ $stats['totalClients'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Администраторов</dt>
                        <dd class="text-2xl font-semibold text-gray-900">{{ $stats['totalAdmins'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Быстрые ссылки -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Управление</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <a href="{{ route('admin.users.index') }}" class="block p-4 border-2 border-primary rounded-lg hover:bg-blue-50 transition">
                <div class="font-semibold text-primary">Пользователи</div>
                <div class="text-sm text-gray-600">Управление пользователями системы</div>
            </a>
            <a href="#" class="block p-4 border-2 border-green-500 rounded-lg hover:bg-green-50 transition">
                <div class="font-semibold text-green-600">Настройки</div>
                <div class="text-sm text-gray-600">Конфигурация системы (в разработке)</div>
            </a>
        </div>
    </div>
</div>
@endsection
