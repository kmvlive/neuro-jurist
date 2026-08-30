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
                Выберите модель для ответов в чате. Текущая: <code class="bg-gray-100 px-2 py-0.5 rounded text-xs">{{ $aiModel }}</code>
            </p>
            <select name="ai_model" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                <optgroup label="⭐ DeepSeek (стабильные, проверенные)">
                    <option value="deepseek/deepseek-v4-flash" {{ $aiModel == 'deepseek/deepseek-v4-flash' ? 'selected' : '' }}>
                        DeepSeek V4 Flash — быстрый, качественный (рекомендуется)
                    </option>
                    <option value="deepseek/deepseek-v4-pro" {{ $aiModel == 'deepseek/deepseek-v4-pro' ? 'selected' : '' }}>
                        DeepSeek V4 Pro — мощнее, дороже
                    </option>
                </optgroup>
                <optgroup label="🚀 Qwen Flash (быстрые и дешёвые, Alibaba)">
                    <option value="dashscope/qwen3.5-flash" {{ $aiModel == 'dashscope/qwen3.5-flash' ? 'selected' : '' }}>
                        Qwen 3.5 Flash — быстрая и дешёвая ✅
                    </option>
                    <option value="dashscope/qwen3.6-flash" {{ $aiModel == 'dashscope/qwen3.6-flash' ? 'selected' : '' }}>
                        Qwen 3.6 Flash — новее ✅
                    </option>
                    <option value="dashscope/qwen-flash" {{ $aiModel == 'dashscope/qwen-flash' ? 'selected' : '' }}>
                        Qwen Flash — базовая
                    </option>
                </optgroup>
                <optgroup label="💎 Qwen Plus/Max (мощные, Alibaba)">
                    <option value="dashscope/qwen3.5-plus" {{ $aiModel == 'dashscope/qwen3.5-plus' ? 'selected' : '' }}>
                        Qwen 3.5 Plus — баланс (медленнее)
                    </option>
                    <option value="dashscope/qwen3.6-plus" {{ $aiModel == 'dashscope/qwen3.6-plus' ? 'selected' : '' }}>
                        Qwen 3.6 Plus — новее (медленнее)
                    </option>
                    <option value="dashscope/qwen3.7-max" {{ $aiModel == 'dashscope/qwen3.7-max' ? 'selected' : '' }}>
                        Qwen 3.7 Max — максимальное качество (медленно)
                    </option>
                    <option value="dashscope/qwen3-max" {{ $aiModel == 'dashscope/qwen3-max' ? 'selected' : '' }}>
                        Qwen 3 Max — мощная
                    </option>
                </optgroup>
                <optgroup label="🔥 Gemini (Google)">
                    <option value="gemini/gemini-3.5-flash" {{ $aiModel == 'gemini/gemini-3.5-flash' ? 'selected' : '' }}>
                        Gemini 3.5 Flash — быстрая
                    </option>
                    <option value="gemini/gemini-3.6-flash" {{ $aiModel == 'gemini/gemini-3.6-flash' ? 'selected' : '' }}>
                        Gemini 3.6 Flash
                    </option>
                    <option value="gemini/gemini-3.7-flash" {{ $aiModel == 'gemini/gemini-3.7-flash' ? 'selected' : '' }}>
                        Gemini 3.7 Flash
                    </option>
                    <option value="gemini/gemini-2.5-pro" {{ $aiModel == 'gemini/gemini-2.5-pro' ? 'selected' : '' }}>
                        Gemini 2.5 Pro
                    </option>
                </optgroup>
                <optgroup label="🧠 Claude (Anthropic)">
                    <option value="anthropic/claude-sonnet-4-5" {{ $aiModel == 'anthropic/claude-sonnet-4-5' ? 'selected' : '' }}>
                        Claude Sonnet 4.5 — мощная
                    </option>
                    <option value="anthropic/claude-sonnet-4-6" {{ $aiModel == 'anthropic/claude-sonnet-4-6' ? 'selected' : '' }}>
                        Claude Sonnet 4.6
                    </option>
                    <option value="anthropic/claude-sonnet-5" {{ $aiModel == 'anthropic/claude-sonnet-5' ? 'selected' : '' }}>
                        Claude Sonnet 5
                    </option>
                    <option value="anthropic/claude-opus-5" {{ $aiModel == 'anthropic/claude-opus-5' ? 'selected' : '' }}>
                        Claude Opus 5 — топ
                    </option>
                </optgroup>
                <optgroup label="🔮 GPT (OpenAI)">
                    <option value="openai/gpt-4.1" {{ $aiModel == 'openai/gpt-4.1' ? 'selected' : '' }}>
                        GPT 4.1
                    </option>
                    <option value="openai/gpt-4.1-mini" {{ $aiModel == 'openai/gpt-4.1-mini' ? 'selected' : '' }}>
                        GPT 4.1 Mini
                    </option>
                    <option value="openai/gpt-5" {{ $aiModel == 'openai/gpt-5' ? 'selected' : '' }}>
                        GPT 5
                    </option>
                    <option value="openai/gpt-5-mini" {{ $aiModel == 'openai/gpt-5-mini' ? 'selected' : '' }}>
                        GPT 5 Mini
                    </option>
                    <option value="openai/gpt-5.1" {{ $aiModel == 'openai/gpt-5.1' ? 'selected' : '' }}>
                        GPT 5.1
                    </option>
                </optgroup>
                <optgroup label="🤖 YandexGPT">
                    <option value="yandex/yandexgpt-lite" {{ $aiModel == 'yandex/yandexgpt-lite' ? 'selected' : '' }}>
                        YandexGPT Lite
                    </option>
                    <option value="yandex/yandexgpt-pro-5.1" {{ $aiModel == 'yandex/yandexgpt-pro-5.1' ? 'selected' : '' }}>
                        YandexGPT Pro 5.1
                    </option>
                </optgroup>
            </select>
            <p class="text-xs text-gray-500 mt-2">
                💡 Для юридического ассистента рекомендуются: <code>DeepSeek V4 Flash</code> или <code>Qwen 3.5/3.6 Flash</code>.
                Модели Plus/Max отвечают точнее, но медленнее.
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
