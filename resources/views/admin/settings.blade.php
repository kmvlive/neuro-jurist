@extends('layouts.app')

@section('title', 'Настройки — Админ-панель')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-primary hover:underline">← Админ-панель</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-4">⚙️ Настройки сайта</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-2">Коды счётчиков аналитики</h2>
        <p class="text-sm text-gray-500 mb-4">
            Вставьте полный код Яндекс Метрики (теги <code>&lt;script&gt;</code> и <code>&lt;noscript&gt;</code>).
            Код будет автоматически добавлен на все страницы сайта.
        </p>
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            <textarea name="counter_code" rows="12"
                      class="w-full border border-gray-300 rounded px-3 py-2 font-mono text-xs"
                      placeholder="<!-- Yandex.Metrika counter --> ...">{{ $counterCode }}</textarea>
            <button type="submit" class="mt-4 w-full bg-primary text-white py-2 rounded-lg hover:bg-blue-700 font-medium">
                Сохранить
            </button>
        </form>
    </div>
</div>
@endsection
