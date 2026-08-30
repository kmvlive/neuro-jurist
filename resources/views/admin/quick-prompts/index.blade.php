@extends('layouts.app')

@section('title', 'Quick-промпты — Админ-панель')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <h1 class="text-3xl font-bold text-gray-900">🎯 Quick-промпты и реклама</h1>
        <div class="flex gap-2 flex-wrap">
            <form method="POST" action="{{ route('admin.quick-prompts.toggle-all-ads') }}" class="inline">
                @csrf
                <input type="hidden" name="action" value="enable">
                <button type="submit" class="bg-green-500 text-white px-4 py-3 rounded-lg hover:bg-green-600 text-sm font-medium">✓ Включить всю рекламу</button>
            </form>
            <form method="POST" action="{{ route('admin.quick-prompts.toggle-all-ads') }}" class="inline">
                @csrf
                <input type="hidden" name="action" value="disable">
                <button type="submit" onclick="return confirm('Отключить всю рекламу?')" class="bg-red-500 text-white px-4 py-3 rounded-lg hover:bg-red-600 text-sm font-medium">✕ Отключить всю рекламу</button>
            </form>
            <a href="{{ route('admin.prompt-categories.index') }}" class="bg-indigo-500 text-white px-4 py-3 rounded-lg hover:bg-indigo-600 text-sm font-medium">📚 Категории</a>
            <a href="{{ route('admin.quick-prompts.create') }}" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 text-center font-medium">+ Создать промпт</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 text-sm text-yellow-800">
        <strong>Как работает:</strong> Незарегистрированные пользователи видят рекламу между 14 и 15 сообщением. Привяжите рекламу к каждому промпту ниже.
    </div>

    <div class="space-y-3">
        @forelse($prompts as $p)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span class="text-2xl">{{ $p->icon }}</span>
                            <span class="font-semibold text-gray-900">{{ $p->title }}</span>
                            <span class="text-xs bg-gray-100 px-2 py-1 rounded font-mono">{{ $p->key }}</span>
                            @if($p->categories->count() > 0)
                                @foreach($p->categories as $cat)
                                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded">
                                        {{ $cat->icon }} {{ $cat->parent ? $cat->parent->name . ' → ' : '' }}{{ $cat->name }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded">Без категории</span>
                            @endif
                            @if(!$p->active)
                                <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded">Неактивен</span>
                            @endif
                            @if(!$p->show_in_chat)
                                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded">🔒 Только в каталоге</span>
                            @else
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">💬 В чате</span>
                            @endif
                        </div>
                        <div class="text-sm mb-2">
                            @if($p->ad && $p->ad->active)
                                <span class="text-green-600">✓ Реклама настроена</span>
                            @else
                                <span class="text-orange-600">⚠ Реклама не настроена</span>
                            @endif
                        </div>
                        <div class="inline-flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300">
                            <span>{{ $p->icon }}</span>
                            <span>{{ $p->title }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0 flex-wrap">
                        <a href="{{ route('admin.quick-prompts.edit', $p) }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">Ред.</a>
                        <a href="{{ route('admin.quick-prompts.ad.edit', $p) }}" class="px-4 py-2 bg-orange-100 text-orange-700 rounded-lg hover:bg-orange-200 text-sm font-medium">📢 Реклама</a>
                        <form method="POST" action="{{ route('admin.quick-prompts.destroy', $p) }}" onsubmit="return confirm('Удалить?')">
                            @csrf @method('DELETE')
                            <button class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 text-sm">Удалить</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">Промптов нет</div>
        @endforelse
    </div>
</div>
@endsection
