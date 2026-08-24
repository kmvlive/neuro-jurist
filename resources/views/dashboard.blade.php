@extends('layouts.app')

@section('title', 'Личный кабинет — Нейро-юрист')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Личный кабинет</h1>
        <p class="text-gray-600 mt-2">Добро пожаловать, {{ auth()->user()->name }}!</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <!-- Статистика -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Моя статистика</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Консультаций использовано:</span>
                    <span class="font-semibold">0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Документов проверено:</span>
                    <span class="font-semibold">0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Тариф:</span>
                    <span class="font-semibold text-primary">Старт</span>
                </div>
            </div>
        </div>

        <!-- Быстрые действия -->
        <div class="bg-white rounded-lg shadow p-6 md:col-span-2">
            <h2 class="text-lg font-semibold mb-4">Быстрые действия</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <a href="{{ route('chat.show') }}" class="block p-4 border-2 border-primary rounded-lg hover:bg-blue-50 transition text-center">
                    <div class="text-3xl mb-2">💬</div>
                    <div class="font-semibold">Новая консультация</div>
                    <div class="text-sm text-gray-600">Задать вопрос юристу</div>
                </a>
                <a href="#" class="block p-4 border-2 border-green-500 rounded-lg hover:bg-green-50 transition text-center">
                    <div class="text-3xl mb-2">📄</div>
                    <div class="font-semibold">Анализ документа</div>
                    <div class="text-sm text-gray-600">Проверить договор</div>
                </a>
                <a href="#" class="block p-4 border-2 border-purple-500 rounded-lg hover:bg-purple-50 transition text-center">
                    <div class="text-3xl mb-2">📋</div>
                    <div class="font-semibold">История чатов</div>
                    <div class="text-sm text-gray-600">Просмотреть прошлые консультации</div>
                </a>
                <a href="#" class="block p-4 border-2 border-orange-500 rounded-lg hover:bg-orange-50 transition text-center">
                    <div class="text-3xl mb-2">💳</div>
                    <div class="font-semibold">Оплата и тарифы</div>
                    <div class="text-sm text-gray-600">Управление подпиской</div>
                </a>
            </div>
        </div>
    </div>

    <!-- Последние чаты -->
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Последние консультации</h2>
            <a href="{{ route('chat.show') }}" class="text-primary hover:underline">Все чаты →</a>
        </div>
        <div class="text-center py-8 text-gray-500">
            У вас пока нет консультаций. Начните новую консультацию!
        </div>
    </div>
</div>
@endsection
