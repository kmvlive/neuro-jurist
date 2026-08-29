@extends('layouts.app')

@section('title', 'Категории промтов')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📚 Категории промтов</h1>
        <a href="{{ route('admin.prompt-categories.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Создать категорию</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <table class="w-full">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-4 py-2 text-left text-sm font-semibold">Иконка</th>
                <th class="px-4 py-2 text-left text-sm font-semibold">Название</th>
                <th class="px-4 py-2 text-left text-sm font-semibold">Slug</th>
                <th class="px-4 py-2 text-left text-sm font-semibold">Родитель</th>
                <th class="px-4 py-2 text-center text-sm font-semibold">Порядок</th>
                <th class="px-4 py-2 text-center text-sm font-semibold">Активна</th>
                <th class="px-4 py-2 text-center text-sm font-semibold">Действия</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($categories as $cat)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3 text-2xl">{{ $cat->icon }}</td>
                    <td class="px-4 py-3">
                        <span class="font-semibold text-gray-900 dark:text-white">
                            {{ $cat->parent_id ? '└─ ' : '' }}{{ $cat->name }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $cat->slug }}</td>
                    <td class="px-4 py-3 text-sm">{{ $cat->parent ? $cat->parent->name : '—' }}</td>
                    <td class="px-4 py-3 text-center">{{ $cat->sort_order }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($cat->active)
                            <span class="text-green-600">✅</span>
                        @else
                            <span class="text-gray-400">❌</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center space-x-2">
                        <a href="{{ route('admin.prompt-categories.edit', $cat) }}" class="text-blue-600 hover:underline">Ред.</a>
                        <form action="{{ route('admin.prompt-categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('Удалить категорию?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Удал.</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
