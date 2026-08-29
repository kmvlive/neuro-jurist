@extends('layouts.app')

@section('title', 'Шаблоны документов — Нейро-юрист')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-10">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-3">📄 Шаблоны документов</h1>
        <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Ответьте на несколько вопросов — и получите готовый юридический документ за 1 минуту.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($templates as $t)
            <a href="{{ route('templates.show', $t->key) }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow hover:shadow-lg transition p-6 border border-gray-100 dark:border-gray-700">
                <div class="text-4xl mb-3">{{ $t->icon }}</div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-primary dark:group-hover:text-blue-400 transition">{{ $t->title }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $t->description }}</p>
                <span class="inline-flex items-center text-primary dark:text-blue-400 text-sm font-medium">
                    Заполнить →
                </span>
            </a>
        @endforeach
    </div>

    @auth
    @else
        <div class="mt-10 text-center bg-blue-50 dark:bg-gray-800 rounded-xl p-6 border border-blue-200 dark:border-gray-700">
            <p class="text-gray-700 dark:text-gray-300 mb-3">Чтобы генерировать документы, <a href="{{ route('register') }}" class="text-primary dark:text-blue-400 font-medium hover:underline">зарегистрируйтесь</a> или <a href="{{ route('login') }}" class="text-primary dark:text-blue-400 font-medium hover:underline">войдите</a>.</p>
        </div>
    @endauth
</div>
@endsection
