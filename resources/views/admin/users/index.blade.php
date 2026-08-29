@extends('layouts.app')

@section('title', 'Пользователи — Админ-панель')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">🔍 Пользователи</h1>
            <p class="text-gray-600 mt-2">
                @php
                    $params = [];
                    if (request('q')) $params[] = 'поиск: «' . e(request('q')) . '»';
                    if (request('plan')) $params[] = 'тариф: ' . request('plan');
                    if (request('subscription')) {
                        $map = ['active' => 'активна', 'expired' => 'истекла', 'none' => 'нет подписки'];
                        $params[] = 'подписка: ' . ($map[request('subscription')] ?? request('subscription'));
                    }
                    if (request('role')) $params[] = 'роль: ' . (request('role') === 'admin' ? 'Админ' : 'Клиент');
                @endphp
                Найдено: {{ $users->total() }}{{ $params ? ' (' . implode(', ', $params) . ')' : '' }}
            </p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 text-center font-medium">
            + Добавить пользователя
        </a>
    </div>

    {{-- Форма поиска и фильтров --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="sm:col-span-2 lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Поиск (имя или email)</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Введите имя или email..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Тариф</label>
                <select name="plan" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white">
                    <option value="">Все</option>
                    @foreach(['start' => 'Старт', 'profi' => 'Профи', 'business' => 'Бизнес', 'week' => 'Неделя', 'premium' => 'Премиум'] as $k => $v)
                        <option value="{{ $k }}" {{ request('plan') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Подписка</label>
                <select name="subscription" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white">
                    <option value="">Все</option>
                    <option value="active" {{ request('subscription') === 'active' ? 'selected' : '' }}>Активна</option>
                    <option value="expired" {{ request('subscription') === 'expired' ? 'selected' : '' }}>Истекла</option>
                    <option value="none" {{ request('subscription') === 'none' ? 'selected' : '' }}>Нет подписки</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Роль</label>
                <select name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white">
                    <option value="">Все</option>
                    <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>Клиент</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Админ</option>
                </select>
            </div>
        </div>
        <div class="flex gap-2 mt-4">
            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium">
                🔍 Найти
            </button>
            <a href="{{ route('admin.users.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 font-medium">
                Сбросить
            </a>
            <span class="text-sm text-gray-500 self-center ml-auto">Найдено: {{ $users->total() }}</span>
        </div>
    </form>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- МОБИЛЬНАЯ ВЕРСИЯ: карточки --}}
    <div class="sm:hidden space-y-3">
        @forelse($users as $user)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('admin.users.show', $user) }}" class="font-semibold text-gray-900 hover:text-primary block truncate">
                            {{ $user->name }}
                        </a>
                        <div class="text-sm text-gray-500 truncate">{{ $user->email }}</div>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full ml-2 flex-shrink-0 {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                        {{ $user->role === 'admin' ? 'Админ' : 'Клиент' }}
                    </span>
                </div>
                @if($user->subscription_plan)
                    <div class="text-xs mb-2">
                        <span class="px-2 py-1 rounded-full {{ $user->subscription_ends_at && $user->subscription_ends_at->isFuture() ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($user->subscription_plan) }}
                            @if($user->subscription_ends_at)
                                · до {{ $user->subscription_ends_at->format('d.m.Y') }}
                            @endif
                        </span>
                    </div>
                @endif
                <div class="text-xs text-gray-500 mb-3">ID: {{ $user->id }} • Рег: {{ $user->created_at->format('d.m.Y') }}</div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.users.show', $user) }}" class="flex-1 bg-blue-50 text-primary py-2 rounded text-center text-sm font-medium">Просмотр</a>
                    <a href="{{ route('admin.users.edit', $user) }}" class="flex-1 bg-gray-50 text-gray-700 py-2 rounded text-center text-sm font-medium">Ред.</a>
                    @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="flex-1" onsubmit="return confirm('Удалить {{ $user->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full bg-red-50 text-red-600 py-2 rounded text-sm font-medium">Удалить</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">Пользователей не найдено</div>
        @endforelse
    </div>

    {{-- ДЕСКТОП: таблица --}}
    <div class="hidden sm:block bg-white shadow overflow-hidden rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Имя</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Тариф</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Роль</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Регистрация</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $user->id }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-gray-900 hover:text-primary">{{ $user->name }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            @if($user->subscription_plan)
                                <span class="px-2 text-xs font-semibold rounded-full {{ $user->subscription_ends_at && $user->subscription_ends_at->isFuture() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($user->subscription_plan) }}
                                    @if($user->subscription_ends_at)
                                        <span class="block text-[10px] font-normal mt-0.5">до {{ $user->subscription_ends_at->format('d.m.Y') }}</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                {{ $user->role === 'admin' ? 'Админ' : 'Клиент' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $user->created_at->format('d.m.Y') }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-3">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-blue-600 hover:underline">Просмотр</a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-primary hover:underline">Ред.</a>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Удалить?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Удалить</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Пользователей не найдено</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="mt-6">{{ $users->links() }}</div>
    @endif
</div>
@endsection
