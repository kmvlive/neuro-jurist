@extends('layouts.app')

@section('title', 'Личный кабинет — Нейро-юрист')

@section('content')
@php
    $user = auth()->user();
    $planNames = [
        'start' => 'Старт',
        'profi' => 'Профи',
        'business' => 'Бизнес',
    ];
    $currentPlan = $user->subscription_plan ? ($planNames[$user->subscription_plan] ?? 'Старт') : 'Старт';
    $isUnlimited = $user->hasUnlimitedMessages();
    $hasActiveSub = $user->hasActiveSubscription();
    $remaining = $user->getRemainingFreeMessages();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Личный кабинет</h1>
        <p class="text-gray-600 dark:text-gray-300 mt-2">Добро пожаловать, {{ $user->name }}!</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 dark:bg-green-900/40 border border-green-400 text-green-800 dark:text-green-200 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid md:grid-cols-3 gap-6">
        <!-- Статистика и тариф -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Моя подписка</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-300">Тариф:</span>
                    <span class="font-semibold text-primary dark:text-blue-400">{{ $currentPlan }}</span>
                </div>

                @if($isUnlimited)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-300">Сообщения:</span>
                        <span class="font-semibold text-green-600 dark:text-green-400">♾️ Безлимит</span>
                    </div>
                @elseif($hasActiveSub)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-300">Сообщения:</span>
                        <span class="font-semibold text-green-600 dark:text-green-400">Безлимит</span>
                    </div>
                    @if($user->subscription_ends_at)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Действует до:</span>
                            <span class="text-gray-700 dark:text-gray-200">{{ $user->subscription_ends_at->format('d.m.Y') }}</span>
                        </div>
                    @endif
                @else
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-300">Осталось сообщений:</span>
                        <span class="font-semibold {{ $remaining < 5 ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-100' }}">
                            {{ $remaining }} / 20
                        </span>
                    </div>
                @endif

                <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('pricing') }}" class="block w-full text-center bg-primary text-white py-2 rounded-lg hover:bg-blue-700 font-medium">
                        @if($hasActiveSub || $isUnlimited)
                            Изменить тариф
                        @else
                            Купить подписку
                        @endif
                    </a>
                </div>
            </div>
        </div>

        <!-- Быстрые действия -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 md:col-span-2">
            <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Быстрые действия</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <form method="POST" action="{{ route('chat.create') }}">
                    @csrf
                    <button type="submit" class="w-full p-4 border-2 border-primary rounded-lg hover:bg-blue-50 dark:hover:bg-gray-700 transition text-center">
                        <div class="text-3xl mb-2">💬</div>
                        <div class="font-semibold text-gray-900 dark:text-white">Новая консультация</div>
                        <div class="text-sm text-gray-600 dark:text-gray-300">Задать вопрос юристу</div>
                    </button>
                </form>
                <a href="{{ route('pricing') }}" class="block p-4 border-2 border-orange-500 rounded-lg hover:bg-orange-50 dark:hover:bg-gray-700 transition text-center">
                    <div class="text-3xl mb-2">💳</div>
                    <div class="font-semibold text-gray-900 dark:text-white">Оплата и тарифы</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">Управление подпиской</div>
                </a>
                <a href="{{ route('chat.show') }}" class="block p-4 border-2 border-purple-500 rounded-lg hover:bg-purple-50 dark:hover:bg-gray-700 transition text-center">
                    <div class="text-3xl mb-2">📋</div>
                    <div class="font-semibold text-gray-900 dark:text-white">История чатов</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">Просмотреть прошлые консультации</div>
                </a>
                <form method="POST" action="{{ route('chat.create') }}">
                    @csrf
                    <button type="submit" class="w-full p-4 border-2 border-green-500 rounded-lg hover:bg-green-50 dark:hover:bg-gray-700 transition text-center">
                        <div class="text-3xl mb-2">📄</div>
                        <div class="font-semibold text-gray-900 dark:text-white">Анализ документа</div>
                        <div class="text-sm text-gray-600 dark:text-gray-300">Проверить договор</div>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Последние чаты -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Последние консультации</h2>
            <a href="{{ route('chat.show') }}" class="text-primary dark:text-blue-400 hover:underline">Все чаты →</a>
        </div>
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            У вас пока нет консультаций. Начните новую консультацию!
        </div>
    </div>

    <!-- История платежей -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">История платежей</h2>
        @if($payments->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Дата</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Тариф</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Сумма</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Статус</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($payments as $payment)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ $payment->created_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ $planNames[$payment->plan] ?? ucfirst($payment->plan) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">
                                    {{ number_format($payment->amount / 100, 0, ',', ' ') }} ₽
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($payment->status === 'CONFIRMED')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            ✓ Оплачен
                                        </span>
                                    @elseif($payment->status === 'REJECTED')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            ✗ Отклонён
                                        </span>
                                    @elseif($payment->status === 'REFUNDED')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            ↩ Возврат
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $payment->status }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                У вас пока нет платежей.
            </div>
        @endif
    </div>
</div>
@endsection
