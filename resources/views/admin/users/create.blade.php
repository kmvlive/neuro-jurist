@extends('layouts.app')

@section('title', 'Добавить пользователя — Админ-панель')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('admin.users.index') }}" class="text-primary hover:underline">← Назад к пользователям</a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mt-4">Добавить пользователя</h1>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        @csrf
        
        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Имя</label>
                <input type="text" name="name" id="name" required 
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2"
                    value="{{ old('name') }}">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" name="email" id="email" required 
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2"
                    value="{{ old('email') }}">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Пароль</label>
                <input type="password" name="password" id="password" required 
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Подтверждение пароля</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required 
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2">
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Роль</label>
                <select name="role" id="role" required 
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2">
                    <option value="client" {{ old('role') === 'client' ? 'selected' : '' }}>Клиент</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Администратор</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-4 pt-4">
                <a href="{{ route('admin.users.index') }}" 
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-900">
                    Отмена
                </a>
                <button type="submit" 
                    class="px-4 py-2 bg-primary text-white rounded-md text-sm font-medium hover:bg-blue-700">
                    Создать
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
