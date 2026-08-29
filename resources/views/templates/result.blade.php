@extends('layouts.app')

@section('title', 'Готовый документ — Нейро-юрист')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 sm:p-8 border border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-3 mb-6">
            <div class="text-4xl">✅</div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Документ готов!</h1>
                <p class="text-gray-600 dark:text-gray-400">Скопируйте текст ниже или сохраните как PDF.</p>
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-4 max-h-[500px] overflow-y-auto">
            <pre class="whitespace-pre-wrap text-sm text-gray-800 dark:text-gray-200 font-mono leading-relaxed">{{ $document }}</pre>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button onclick="copyDocument()" class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-3 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 font-medium">
                📋 Копировать текст
            </button>
            <button onclick="window.print()" class="flex-1 bg-primary text-white px-4 py-3 rounded-lg hover:bg-blue-700 font-medium">
                🖨️ Печать / Сохранить в PDF
            </button>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('templates.index') }}" class="text-primary dark:text-blue-400 hover:underline">← Вернуться к шаблонам</a>
        </div>
    </div>
</div>

<script>
function copyDocument() {
    const text = document.querySelector('pre').innerText;
    navigator.clipboard.writeText(text).then(() => {
        alert('Текст скопирован в буфер обмена!');
    });
}
</script>
@endsection
