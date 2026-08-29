@extends('layouts.app')

@section('title', 'Тарифы — Админ-панель')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-primary hover:underline">← Админ-панель</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-4">Управление тарифами</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Тариф</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Цена</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Популярный</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Активен</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($plans as $plan)
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900">{{ $plan->name }}</div>
                        <div class="text-sm text-gray-500">ключ: {{ $plan->key }}</div>
                    </td>
                    <td class="px-6 py-4 text-gray-900">
                        @if($plan->price == 0)
                            <span class="text-green-600 font-medium">Бесплатно</span>
                        @else
                            {{ number_format($plan->price, 0, '.', ' ') }} {{ $plan->currency }}/{{ $plan->period }}
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($plan->highlighted)
                            <span class="text-blue-600 font-medium">★ Да</span>
                        @else
                            <span class="text-gray-400">Нет</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($plan->is_active)
                            <span class="text-green-600 font-medium">✓ Да</span>
                        @else
                            <span class="text-red-600 font-medium">✗ Нет</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.plans.edit', $plan) }}" class="text-primary hover:underline mr-4">Редактировать</a>
                        <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" class="inline"
                              onsubmit="return confirm('Удалить тариф «{{ $plan->name }}»?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Удалить</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">Тарифов пока нет</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Добавить новый тариф</h2>
        <form method="POST" action="{{ route('admin.plans.store') }}" class="grid md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ключ (латиницей, без пробелов)</label>
                <input type="text" name="key" required placeholder="premium"
                    class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary">
                @error('key')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Название</label>
                <input type="text" name="name" required placeholder="Премиум"
                    class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Цена, ₽ (0 = бесплатно)</label>
                <input type="number" name="price" required min="0" placeholder="1990"
                    class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-primary text-white py-2 rounded-md hover:bg-blue-700">
                    Добавить тариф
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
