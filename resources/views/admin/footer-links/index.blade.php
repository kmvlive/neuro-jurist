@extends('layouts.app')

@section('title', 'Меню футера — Админ-панель')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-primary hover:underline">← Админ-панель</a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mt-4">Меню футера</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid md:grid-cols-2 gap-6">
        <!-- Список ссылок -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Существующие ссылки</h2>
            <div class="space-y-3">
                @forelse($links as $link)
                    <div class="border border-gray-200 dark:border-gray-700 rounded p-3 flex justify-between items-center">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $link->title }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $link->url }}</div>
                            @if($link->is_external)
                                <span class="text-xs text-blue-600">внешняя</span>
                            @endif
                            @if(!$link->is_active)
                                <span class="text-xs text-red-600">скрыта</span>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.footer-links.edit', $link) }}" class="text-primary hover:underline text-sm">Изменить</a>
                            <form method="POST" action="{{ route('admin.footer-links.destroy', $link) }}" onsubmit="return confirm('Удалить?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-sm">Удалить</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">Пока нет ссылок</p>
                @endforelse
            </div>
        </div>

        <!-- Форма добавления -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Добавить ссылку</h2>
            <form method="POST" action="{{ route('admin.footer-links.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Название</label>
                    <input type="text" name="title" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2" placeholder="О нас">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL</label>
                    <input type="text" name="url" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2" placeholder="/about или https://...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Порядок сортировки</label>
                    <input type="number" name="sort_order" value="0" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_external" id="is_external" class="mr-2">
                    <label for="is_external" class="text-sm text-gray-700 dark:text-gray-300">Внешняя ссылка (открывать в новой вкладке)</label>
                </div>
                <button type="submit" class="w-full bg-primary text-white py-2 rounded hover:bg-blue-700">Добавить</button>
            </form>
        </div>
    </div>
</div>
@endsection
