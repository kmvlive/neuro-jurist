@extends('layouts.app')

@section('title', $category ? 'Редактировать категорию' : 'Создать категорию')

@section('content')
<div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white dark:text-white">
        {{ $category ? 'Редактировать категорию' : 'Создать категорию' }}
    </h1>

    <form action="{{ $category ? route('admin.prompt-categories.update', $category) : route('admin.prompt-categories.store') }}" method="POST" class="space-y-4">
        @csrf
        @if($category) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300 dark:text-gray-300">Родительская категория (для подраздела)</label>
            <select name="parent_id" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <option value="">— Корневой раздел —</option>
                @foreach($parents as $p)
                    <option value="{{ $p->id }}" @selected(old('parent_id', $category?->parent_id) == $p->id)>
                        {{ $p->icon }} {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300 dark:text-gray-300">Название *</label>
            <input type="text" name="name" value="{{ old('name', $category?->name) }}" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300 dark:text-gray-300">Slug *</label>
            <input type="text" name="slug" value="{{ old('slug', $category?->slug) }}" required pattern="[a-z0-9_-]+" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Только латиница, цифры, дефис и подчёркивание</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300 dark:text-gray-300">Иконка (emoji)</label>
            <input type="text" name="icon" value="{{ old('icon', $category?->icon) }}" maxlength="10" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300 dark:text-gray-300">Порядок сортировки</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $category?->sort_order ?? 0) }}" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" id="active" value="1" @checked(old('active', $category?->active ?? true)) class="rounded">
            <label for="active" class="text-gray-700 dark:text-gray-300 dark:text-gray-300">Активна</label>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Сохранить</button>
            <a href="{{ route('admin.prompt-categories.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400">Отмена</a>
        </div>
    </form>
</div>
@endsection
