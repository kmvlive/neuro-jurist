@extends('layouts.app')

@section('title', $user->name . ' — Админ-панель')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('admin.users.index') }}" class="text-primary hover:underline">← К списку пользователей</a>

    <div class="mt-4 bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
                <p class="text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                <div class="mt-2">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                        {{ $user->role === 'admin' ? 'Администратор' : 'Клиент' }}
                    </span>
                    <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">ID: {{ $user->id }}</span>
                </div>
            </div>
            <a href="{{ route('admin.users.edit', $user) }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-center">Редактировать</a>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Регистрация</div>
            <div class="text-lg font-semibold">{{ $user->created_at->format('d.m.Y') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Тариф</div>
            <div class="text-lg font-semibold">{{ $user->subscription_plan ?: '—' }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Подписка до</div>
            <div class="text-lg font-semibold">
                @if($user->subscription_ends_at)
                    {{ \Carbon\Carbon::parse($user->subscription_ends_at)->format('d.m.Y') }}
                    @if(\Carbon\Carbon::parse($user->subscription_ends_at)->isPast())
                        <span class="text-xs text-red-600 ml-1">(истекла)</span>
                    @else
                        <span class="text-xs text-green-600 ml-1">(активна)</span>
                    @endif
                @else
                    —
                @endif
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Сообщений всего</div>
            <div class="text-lg font-semibold">{{ $totalMessages }}</div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h2 class="font-semibold text-lg mb-4">Чаты ({{ $chats->count() }} последних)</h2>
            <div class="space-y-2">
                @forelse($chats as $chat)
                    <div class="border-b border-gray-100 pb-2 text-sm">
                        <div class="font-medium">{{ $chat->title ?: 'Без названия' }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $chat->created_at->format('d.m.Y H:i') }}</div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Чатов нет</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h2 class="font-semibold text-lg mb-4">Платежи ({{ $payments->count() }} последних)</h2>
            <div class="space-y-2">
                @forelse($payments as $p)
                    <div class="border-b border-gray-100 pb-2 text-sm flex justify-between">
                        <div>
                            <div class="font-medium">{{ number_format($p->amount, 0, ',', ' ') }} ₽</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $p->created_at->format('d.m.Y H:i') }}</div>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $p->status === 'CONFIRMED' ? 'bg-green-100 text-green-800' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                            {{ $p->status }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Платежей нет</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
