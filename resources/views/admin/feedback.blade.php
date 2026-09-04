@extends('layouts.app')

@section('title', 'Отзывы — Админ-панель')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-primary hover:underline">← Админ-панель</a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mt-4">💬 Отзывы о ответах AI</h1>
    </div>

    {{-- Статистика --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Всего оценок</div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $stats['up'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">👍 Полезно</div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-red-600">{{ $stats['down'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">👎 Плохо</div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['with_comments'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">С комментарием</div>
        </div>
    </div>

    @if($stats['total'] > 0)
        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Рейтинг полезности:
            <span class="font-bold {{ ($stats['up'] / max($stats['total'],1)) >= 0.7 ? 'text-green-600' : 'text-orange-600' }}">
                {{ round($stats['up'] / max($stats['total'], 1) * 100) }}% 👍
            </span>
        </div>
    @endif

    {{-- Фильтры --}}
    <div class="flex gap-2 mb-6">
        <a href="{{ route('admin.feedback') }}"
           class="px-4 py-2 rounded-lg text-sm font-medium {{ $filter === 'all' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            Все ({{ $stats['total'] }})
        </a>
        <a href="{{ route('admin.feedback', ['filter' => 'up']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium {{ $filter === 'up' ? 'bg-green-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            👍 ({{ $stats['up'] }})
        </a>
        <a href="{{ route('admin.feedback', ['filter' => 'down']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium {{ $filter === 'down' ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            👎 ({{ $stats['down'] }})
        </a>
    </div>

    {{-- Список отзывов --}}
    @forelse($feedbacks as $fb)
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5 mb-4 border-l-4 {{ $fb->vote === 1 ? 'border-green-500' : 'border-red-500' }}">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">{{ $fb->vote === 1 ? '👍' : '👎' }}</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $fb->user ? $fb->user->name : 'Гость' }}
                    </span>
                    @if($fb->message && $fb->message->chat)
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            · чат: {{ $fb->message->chat->title ?? 'Без названия' }}
                        </span>
                    @endif
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $fb->created_at->format('d.m.Y H:i') }}</span>
            </div>

            @if($fb->comment)
                <div class="mb-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded p-3">
                    <div class="text-xs font-semibold text-red-700 dark:text-red-300 mb-1">💬 Комментарий пользователя:</div>
                    <div class="text-sm text-gray-800 dark:text-gray-200">{{ $fb->comment }}</div>
                </div>
            @endif

            @if($fb->message)
                <div class="mb-2">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Вопрос пользователя:</div>
                    <div class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 rounded p-2">
                        {{ \Illuminate\Support\Str::limit(optional($fb->message->chat->messages()->where('role','user')->where('id','<',$fb->message_id)->latest('id')->first())->content ?? '—', 200) }}
                    </div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Ответ AI:</div>
                    <div class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 rounded p-2 whitespace-pre-wrap">{{ \Illuminate\Support\Str::limit($fb->message->content, 400) }}</div>
                </div>
            @endif
        </div>
    @empty
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-8 text-center text-gray-500 dark:text-gray-400">
            Пока нет отзывов с таким фильтром
        </div>
    @endforelse

    {{ $feedbacks->links() }}
</div>
@endsection
