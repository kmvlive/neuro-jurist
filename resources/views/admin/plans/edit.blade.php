@extends('layouts.app')

@section('title', 'Редактирование тарифа — Админ-панель')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('admin.plans.index') }}" class="text-primary hover:underline">← Назад к тарифам</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-4">Редактирование тарифа «{{ $plan->name }}»</h1>
        <p class="text-gray-500 mt-1">Ключ: {{ $plan->key }} (используется в коде, менять нельзя)</p>
    </div>

    <form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="bg-white shadow rounded-lg p-6">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Название тарифа</label>
                    <input type="text" name="name" required value="{{ old('name', $plan->name) }}"
                        class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Цена, ₽ (0 = бесплатно)</label>
                    <input type="number" name="price" required min="0" value="{{ old('price', $plan->price) }}"
                        class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">💰 Старая цена (зачёркнутая, оставьте пустым если нет скидки)</label>
                    <input type="number" name="old_price" min="0" value="{{ old('old_price', $plan->old_price) }}" placeholder="1990"
                        class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary">
                    <p class="text-xs text-gray-500 mt-1">Отобразится зачёркнутой рядом с актуальной ценой</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">⏱ Длительность в днях</label>
                    <input type="number" name="duration_days" required min="1" value="{{ old("duration_days", $plan->duration_days) }}"
                        class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary">
                    <p class="text-xs text-gray-500 mt-1">7 = неделя, 30 = месяц, 365 = год</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Период (мес, год, бесплатно)</label>
                    <input type="text" name="period" required value="{{ old('period', $plan->period) }}"
                        class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Валюта</label>
                    <input type="text" name="currency" required value="{{ old('currency', $plan->currency) }}"
                        class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">✅ Преимущества (каждое с новой строки)</label>
                <textarea name="features" rows="8"
                    class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary">{{ implode("\n", $plan->features ?? []) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Каждая строка — отдельный пункт с зелёной галочкой на странице тарифов</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">❌ Ограничения (каждое с новой строки, можно оставить пустым)</label>
                <textarea name="limitations" rows="4"
                    class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary">{{ implode("\n", $plan->limitations ?? []) }}</textarea>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Текст кнопки</label>
                    <input type="text" name="button_text" required value="{{ old('button_text', $plan->button_text) }}"
                        class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Порядок отображения (0 — первый)</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}"
                        class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div class="space-y-3 border-t border-gray-200 pt-4">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="highlighted" value="1" {{ $plan->highlighted ? 'checked' : '' }}
                        class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-primary">
                    <span class="text-sm font-medium text-gray-700">★ Пометить как «Популярный» (синяя рамка и плашка)</span>
                </label>
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }}
                        class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-primary">
                    <span class="text-sm font-medium text-gray-700">✓ Тариф активен (виден на сайте)</span>
                </label>
            </div>

            <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.plans.index') }}"
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Отмена
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-primary text-white rounded-md text-sm font-medium hover:bg-blue-700">
                    Сохранить изменения
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
