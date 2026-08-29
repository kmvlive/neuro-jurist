@extends('layouts.app')

@section('title', $template->title . ' — Нейро-юрист')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('templates.index') }}" class="text-primary dark:text-blue-400 hover:underline text-sm mb-4 inline-block">← Все шаблоны</a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 sm:p-8 border border-gray-100 dark:border-gray-700">
        <div class="flex items-start gap-4 mb-6">
            <div class="text-5xl">{{ $template->icon }}</div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $template->title }}</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $template->description }}</p>
            </div>
        </div>

        @if(session('error'))
            <div class="bg-red-100 dark:bg-red-900/40 border border-red-400 text-red-800 dark:text-red-200 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('templates.generate', $template->key) }}">
            @csrf
            <div class="space-y-5">
                @foreach($template->questions as $q)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ $q['label'] }}
                            @if(!empty($q['required']))
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        @if($q['type'] === 'textarea')
                            <textarea name="{{ $q['key'] }}" rows="4" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white" placeholder="{{ $q['placeholder'] ?? '' }}" {{ !empty($q['required']) ? 'required' : '' }}>{{ old($q['key']) }}</textarea>
                        @elseif($q['type'] === 'select')
                            <select name="{{ $q['key'] }}" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white" {{ !empty($q['required']) ? 'required' : '' }}>
                                <option value="">Выберите...</option>
                                @foreach($q['options'] ?? [] as $opt)
                                    <option value="{{ $opt }}" {{ old($q['key']) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="{{ $q['type'] }}" name="{{ $q['key'] }}" value="{{ old($q['key']) }}" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white" placeholder="{{ $q['placeholder'] ?? '' }}" {{ !empty($q['required']) ? 'required' : '' }}>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex items-center justify-between">
                <a href="{{ route('templates.index') }}" class="text-gray-500 dark:text-gray-400 hover:underline text-sm">Отмена</a>
                <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium">
                    📄 Сгенерировать документ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
