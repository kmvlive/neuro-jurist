@extends('layouts.app')

@section('title', 'Редактировать пользователя — Админ-панель')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('admin.users.index') }}" class="text-primary hover:underline">← Назад к пользователям</a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mt-4">Редактировать пользователя</h1>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Имя</label>
                <input type="text" name="name" id="name" required
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2"
                    value="{{ old('name', $user->name) }}">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" name="email" id="email" required
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2"
                    value="{{ old('email', $user->email) }}">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Пароль (оставьте пустым, чтобы не менять)</label>
                <input type="password" name="password" id="password"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Подтверждение пароля</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2">
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Роль</label>
                <select name="role" id="role" required
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2">
                    <option value="client" {{ old('role', $user->role) === 'client' ? 'selected' : '' }}>Клиент</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Администратор</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="hidden" name="unlimited_messages" value="0">
                    <input type="checkbox" name="unlimited_messages" id="unlimited_messages" value="1"
                        {{ old('unlimited_messages', $user->unlimited_messages) ? 'checked' : '' }}
                        class="mt-0.5 h-5 w-5 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-primary">
                    <span>
                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">♾️ Безлимитные сообщения</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Пользователь остаётся на тарифе «Старт», но может писать без ограничения в 20 сообщений.</span>
                    </span>
                </label>
            </div>

            <div class="flex justify-end space-x-4 pt-4">
                <a href="{{ route('admin.users.index') }}"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-900">
                    Отмена
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-primary text-white rounded-md text-sm font-medium hover:bg-blue-700">
                    Сохранить
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
