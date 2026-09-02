@extends('layouts.app')

@section('title', ($prompt ? 'Редактировать' : 'Создать') . ' промпт — Админ-панель')

@section('content')
<div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('admin.quick-prompts.index') }}" class="text-primary hover:underline">← К промптам</a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-4 mb-6">{{ $prompt ? 'Редактировать промпт' : 'Новый промпт' }}</h1>

    <form method="POST" action="{{ $prompt ? route('admin.quick-prompts.update', $prompt) : route('admin.quick-prompts.store') }}" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-4">
        @csrf
        @if($prompt) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Иконка (эмодзи)</label>
            <input type="text" name="icon" value="{{ old('icon', $prompt?->icon ?? '📄') }}" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-2xl text-center" maxlength="10">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Название</label>
            <input type="text" name="title" value="{{ old('title', $prompt?->title) }}" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2" placeholder="Претензия в магазин">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ключ (латиница, для кода)</label>
            <input type="text" name="key" value="{{ old('key', $prompt?->key) }}" required pattern="[a-z0-9_]+" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 font-mono" placeholder="claim_shop">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Только строчные буквы, цифры и подчёркивания</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст промта (инструкция для AI)</label>
            <textarea name="text" rows="6" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2" placeholder="Действуй как опытный юрист по авторскому праву. Пользователь - фотограф, чьи работы использовали без разрешения. Помоги составить претензию...">{{ old('text', $prompt?->text) }}</textarea>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Этот текст отправится как первое сообщение пользователя при клике на промпт. Оставь пустым - отправится только название темы.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Разделы в каталоге (можно выбрать несколько)</label>
            <div class="border border-gray-300 dark:border-gray-600 rounded p-3 max-h-60 overflow-y-auto bg-gray-50 dark:bg-gray-900 space-y-1">
                @foreach($categories as $cat)
                    <label class="flex items-center gap-2 py-1 px-2 hover:bg-white dark:bg-gray-800 rounded cursor-pointer">
                        <input type="checkbox" name="categories[]" value="{{ $cat->id }}" 
                            @checked(in_array($cat->id, old('categories', $prompt ? $prompt->categories->pluck('id')->toArray() : [])))
                            class="rounded">
                        <span>
                            {{ $cat->parent ? '└─ ' : '' }}{{ $cat->icon }} {{ $cat->name }}
                        </span>
                    </label>
                @endforeach
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Управление разделами: <a href="{{ route('admin.prompt-categories.index') }}" class="text-primary hover:underline">Категории промтов →</a>
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Порядок сортировки</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $prompt?->sort_order ?? 0) }}" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2">
        </div>

        <label class="flex items-center gap-2">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" {{ old('active', $prompt?->active ?? true) ? 'checked' : '' }} class="rounded">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Активен</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="hidden" name="show_in_chat" value="0">
            <input type="checkbox" name="show_in_chat" value="1" {{ old('show_in_chat', $prompt?->show_in_chat ?? true) ? 'checked' : '' }} class="rounded">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Показывать в чате (на главной)</span>
        </label>

        <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg hover:bg-blue-700 font-medium">Сохранить</button>
    </form>
</div>
@endsection
