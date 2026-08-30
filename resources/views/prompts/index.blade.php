@extends('layouts.app')

@section('title', 'Каталог консультаций')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    {{-- Заголовок --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">📚 Каталог консультаций</h1>
        <p class="text-gray-600 dark:text-gray-400">Выберите раздел для просмотра доступных тем. Всего: <span class="font-semibold">{{ $totalPrompts }}</span> консультаций</p>
    </div>

    {{-- Разделы --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($sections as $section)
            <a href="{{ route('prompts.show', $section->slug) }}"
               class="group block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-500 hover:shadow-lg transition overflow-hidden">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-750 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">{{ $section->icon }}</span>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                            {{ $section->name }}
                        </h2>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Консультаций в разделе:</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $section->total_prompts }}</span>
                    </div>
                    @if($section->description)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 line-clamp-2">{{ $section->description }}</p>
                    @endif
                </div>
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Открыть раздел</span>
                    <span class="text-blue-600 dark:text-blue-400 group-hover:translate-x-1 transition">→</span>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Кнопка назад --}}
    <div class="mt-8 text-center">
        <a href="{{ route('chat.show') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition">
            ← Вернуться к чату
        </a>
    </div>
</div>
@endsection
