@extends('layouts.app')

@section('title', 'Редактировать ссылку — Админ-панель')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('admin.footer-links.index') }}" class="text-primary hover:underline">← Меню футера</a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mt-4">Редактировать ссылку</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <form method="POST" action="{{ route('admin.footer-links.update', $footerLink) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Название</label>
                <input type="text" name="title" value="{{ $footerLink->title }}" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL</label>
                <input type="text" name="url" value="{{ $footerLink->url }}" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Порядок сортировки</label>
                <input type="number" name="sort_order" value="{{ $footerLink->sort_order }}" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2">
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="is_external" id="is_external" {{ $footerLink->is_external ? 'checked' : '' }} class="mr-2">
                <label for="is_external" class="text-sm text-gray-700 dark:text-gray-300">Внешняя ссылка</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" {{ $footerLink->is_active ? 'checked' : '' }} class="mr-2">
                <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Активна</label>
            </div>
            <button type="submit" class="w-full bg-primary text-white py-2 rounded hover:bg-blue-700">Сохранить</button>
        </form>
    </div>
</div>
@endsection
