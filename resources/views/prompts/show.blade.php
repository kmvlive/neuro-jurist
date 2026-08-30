@extends('layouts.app')

@section('title', $section->name . ' — Каталог')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    {{-- Хлебные крошки --}}
    <div class="mb-6 text-sm text-gray-600 dark:text-gray-400">
        <a href="{{ route('prompts.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition">📚 Каталог</a>
        <span class="mx-2">›</span>
        <span class="text-gray-900 dark:text-white font-medium">{{ $section->name }}</span>
    </div>

    {{-- Заголовок раздела --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-750 px-6 py-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <span class="text-5xl">{{ $section->icon }}</span>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $section->name }}</h1>
                    @if($section->description)
                        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $section->description }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Промты без подраздела --}}
    @if($section->quickPrompts->count() > 0)
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Общие темы</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($section->quickPrompts as $prompt)
                    @include('prompts._card', ['prompt' => $prompt])
                @endforeach
            </div>
        </div>
    @endif

    {{-- Подразделы --}}
    @foreach($section->children as $sub)
        @if($sub->quickPrompts->count() > 0)
            <div class="mb-8 last:mb-0">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="text-blue-600 dark:text-blue-400">└─</span>
                    {{ $sub->name }}
                </h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($sub->quickPrompts as $prompt)
                        @include('prompts._card', ['prompt' => $prompt])
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    {{-- Кнопка назад --}}
    <div class="mt-8 text-center">
        <a href="{{ route('prompts.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            ← Все разделы
        </a>
    </div>
</div>
@endsection
