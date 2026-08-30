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

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf

        {{-- AI Модель --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-2 flex items-center gap-2">
                🤖 Модель AI-ассистента
            </h2>
            <p class="text-sm text-gray-500 mb-4">
                Выберите модель для ответов в чате. Смена модели влияет на качество ответов и стоимость.
            </p>
            <select name="ai_model" class="w-full border border-gray-300 rounded px-3 py-2">
                <optgroup label="DeepSeek (текущая, стабильная)">
                    <option value="deepseek/deepseek-v4-flash" {{ $aiModel == 'deepseek/deepseek-v4-flash' ? 'selected' : '' }}>
                        DeepSeek V4 Flash — быстрый, качественный (рекомендуется)
                    </option>
                    <option value="deepseek/deepseek-chat" {{ $aiModel == 'deepseek/deepseek-chat' ? 'selected' : '' }}>
                        DeepSeek Chat — базовая модель
                    </option>
                </optgroup>
                <optgroup label="Qwen (Alibaba, дешевле)">
                    <option value="qwen/qwen-3.5-flash" {{ $aiModel == 'qwen/qwen-3.5-flash' ? 'selected' : '' }}>
                        Qwen 3.5 Flash — быстрая и дешёвая
                    </option>
                    <option value="qwen/qwen-3.6-flash" {{ $aiModel == 'qwen/qwen-3.6-flash' ? 'selected' : '' }}>
                        Qwen 3.6 Flash — новее
                    </option>
                    <option value="qwen/qwen-3.5-plus" {{ $aiModel == 'qwen/qwen-3.5-plus' ? 'selected' : '' }}>
                        Qwen 3.5 Plus — баланс скорость/качество
                    </option>
                    <option value="qwen/qwen-3.7-max" {{ $aiModel == 'qwen/qwen-3.7-max' ? 'selected' : '' }}>
                        Qwen 3.7 Max — максимальное качество
                    </option>
                </optgroup>
            </select>
            <p class="text-xs text-gray-500 mt-2">
                ⚠️ Текущая рабочая модель: <code>deepseek/deepseek-v4-flash</code>. Перед сменой проверьте доступность в <a href="https://timeweb.cloud/docs/ai-agents/pricing/models" target="_blank" class="text-primary hover:underline">документации Timeweb</a>.
            </p>
        </div>

        {{-- Коды счётчиков --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-2">Коды счётчиков аналитики</h2>
            <p class="text-sm text-gray-500 mb-4">
                Вставьте полный код Яндекс Метрики (теги <code>&lt;script&gt;</code> и <code>&lt;noscript&gt;</code>).
                Код будет автоматически добавлен на все страницы сайта.
            </p>
            <textarea name="counter_code" rows="12"
                      class="w-full border border-gray-300 rounded px-3 py-2 font-mono text-xs"
                      placeholder="<!-- Yandex.Metrika counter --> ...">{{ $counterCode }}</textarea>
        </div>

        <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg hover:bg-blue-700 font-medium">
            Сохранить настройки
        </button>
    </form>
</div>
@endsection
