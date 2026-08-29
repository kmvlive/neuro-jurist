@extends('layouts.app')

@section('title', 'Промокоды — Админ-панель')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">🎁 Промокоды</h1>
        <a href="{{ route('admin.promo-codes.create') }}" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 text-center font-medium">+ Создать промокод</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 dark:bg-green-900/40 border border-green-400 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="sm:hidden space-y-3">
        @forelse($promoCodes as $pc)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex justify-between items-start mb-2">
                    <span class="font-mono font-bold text-primary dark:text-blue-400">{{ $pc->code }}</span>
                    <span class="px-2 py-1 text-xs rounded-full {{ $pc->isValid() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $pc->isValid() ? 'Активен' : 'Неактивен' }}
                    </span>
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-300 mb-1">Скидка: <strong>{{ $pc->discount_percent }}%</strong></div>
                <div class="text-sm text-gray-600 dark:text-gray-300 mb-1">Использований: {{ $pc->used_count }}{{ $pc->max_uses ? ' из ' . $pc->max_uses : '' }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-300 mb-2">До: {{ $pc->expires_at ? $pc->expires_at->format('d.m.Y') : 'бессрочно' }}</div>
                
                @if($pc->one_per_user || $pc->new_users_only || $pc->user)
                    <div class="flex flex-wrap gap-1 mb-3">
                        @if($pc->one_per_user)
                            <span class="px-2 py-0.5 text-xs rounded bg-purple-100 dark:bg-purple-900/50 text-purple-800 dark:text-purple-200">1 раз на юзера</span>
                        @endif
                        @if($pc->new_users_only)
                            <span class="px-2 py-0.5 text-xs rounded bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200">Только новым</span>
                        @endif
                        @if($pc->user)
                            <span class="px-2 py-0.5 text-xs rounded bg-orange-100 dark:bg-orange-900/50 text-orange-800 dark:text-orange-200">👤 {{ $pc->user->email }}</span>
                        @endif
                    </div>
                @endif
                
                <div class="flex gap-2">
                    <a href="{{ route('admin.promo-codes.edit', $pc) }}" class="flex-1 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 py-2 rounded text-center text-sm">Ред.</a>
                    <form method="POST" action="{{ route('admin.promo-codes.destroy', $pc) }}" class="flex-1" onsubmit="return confirm('Удалить промокод?')">
                        @csrf @method('DELETE')
                        <button class="w-full bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 py-2 rounded text-sm">Удалить</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center text-gray-500 dark:text-gray-400">Промокодов нет</div>
        @endforelse
    </div>

    <div class="hidden sm:block bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Код</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Скидка</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Использований</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Действует до</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ограничения</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Статус</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($promoCodes as $pc)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 font-mono font-bold text-primary dark:text-blue-400">{{ $pc->code }}</td>
                            <td class="px-6 py-4 text-gray-900 dark:text-gray-100">{{ $pc->discount_percent }}%</td>
                            <td class="px-6 py-4 text-gray-900 dark:text-gray-100">{{ $pc->used_count }}{{ $pc->max_uses ? ' / ' . $pc->max_uses : '' }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $pc->expires_at ? $pc->expires_at->format('d.m.Y') : '—' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @if($pc->one_per_user)
                                        <span class="px-2 py-0.5 text-xs rounded bg-purple-100 dark:bg-purple-900/50 text-purple-800 dark:text-purple-200">1 раз</span>
                                    @endif
                                    @if($pc->new_users_only)
                                        <span class="px-2 py-0.5 text-xs rounded bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200">Новым</span>
                                    @endif
                                    @if($pc->user)
                                        <span class="px-2 py-0.5 text-xs rounded bg-orange-100 dark:bg-orange-900/50 text-orange-800 dark:text-orange-200" title="{{ $pc->user->email }}">👤</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $pc->isValid() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $pc->isValid() ? 'Активен' : 'Неактивен' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <a href="{{ route('admin.promo-codes.edit', $pc) }}" class="text-primary dark:text-blue-400 hover:underline">Ред.</a>
                                <form method="POST" action="{{ route('admin.promo-codes.destroy', $pc) }}" class="inline" onsubmit="return confirm('Удалить?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 dark:text-red-400 hover:underline">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Промокодов нет</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
