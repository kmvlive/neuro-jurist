@extends('layouts.app')

@section('title', 'Реклама: ' . $quickPrompt->title . ' — Админ-панель')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('admin.quick-prompts.index') }}" class="text-primary hover:underline">← К промптам</a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-4 mb-2">📢 Реклама для «{{ $quickPrompt->title }}»</h1>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Будет показана незарегистрированным пользователям между 14 и 15 сообщением</p>

    <form method="POST" action="{{ route('admin.quick-prompts.ad.update', $quickPrompt) }}" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст рекламы</label>
            <textarea name="content" rows="4" required class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 dark:text-white" placeholder="Нужна грамотная претензия? Наши юристы составят её за 500 ₽ с гарантией результата!">{{ old('content', $ad?->content) }}</textarea>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Можно использовать HTML: &lt;b&gt;, &lt;br&gt;, &lt;a href=""&gt;</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст кнопки (необязательно)</label>
            <input type="text" name="cta_text" value="{{ old('cta_text', $ad?->cta_text) }}" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-3 py-2" placeholder="Заказать">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ссылка кнопки (необязательно)</label>
            <input type="text" name="cta_url" value="{{ old('cta_url', $ad?->cta_url) }}" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-3 py-2" placeholder="/pricing">
        </div>

        <label class="flex items-center gap-2">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" {{ old('active', $ad?->active ?? true) ? 'checked' : '' }} class="rounded">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Реклама активна</span>
        </label>

        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded p-4 text-sm text-blue-800 dark:text-blue-200">
            <strong>Предпросмотр (как будет выглядеть в чате):</strong>
            <div class="mt-3 p-4 bg-gradient-to-br from-orange-50 via-yellow-50 to-amber-50 border-l-4 border-orange-400 rounded-lg shadow-sm">
                <div class="flex items-center gap-1 mb-2 text-orange-700 text-xs font-semibold uppercase tracking-wider">
                    <span>📢</span>
                    <span>Рекомендация</span>
                </div>
                <div class="text-sm text-gray-800 dark:text-gray-100 leading-relaxed">
                    @if($ad && $ad->content)
                        {!! $ad->content !!}
                    @else
                        <em class="text-gray-500 dark:text-gray-400">Текст рекламы появится здесь...</em>
                    @endif
                </div>
                @if($ad && $ad->cta_text && $ad->cta_url)
                    <a href="{{ $ad->cta_url }}" class="inline-block mt-3 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded text-sm font-medium">{{ $ad->cta_text }}</a>
                @endif
                <div class="mt-2 text-xs text-gray-400 italic">Информационное сообщение</div>
            </div>
        </div>

        <button type="submit" class="w-full bg-orange-500 text-white py-3 rounded-lg hover:bg-orange-600 font-medium">Сохранить рекламу</button>
    </form>
</div>
@endsection
