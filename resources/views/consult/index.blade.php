@extends('layouts.app')

@section('title', 'Каталог консультаций — Нейро-юрист')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">📚 Каталог юридических консультаций</h1>
    <p class="text-gray-600 dark:text-gray-400 mb-8">Выберите тему — AI-юрист проконсультирует бесплатно</p>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($prompts as $p)
            <a href="{{ route('consult.show', $p->key) }}"
               class="block bg-white dark:bg-gray-800 shadow rounded-lg p-5 hover:shadow-md transition">
                <div class="text-3xl mb-3">{{ $p->icon }}</div>
                <h2 class="font-semibold text-gray-900 dark:text-white mb-1">{{ $p->title }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                    {{ \Illuminate\Support\Str::limit($p->seo_description ?: $p->text, 100) }}
                </p>
            </a>
        @endforeach
    </div>
</div>
@endsection
