@extends('layouts.app')

@section('title', 'Регистрация — Нейро-юрист')

@section('content')
<div class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Регистрация
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Уже есть аккаунт?
                <a href="{{ route('login') }}" class="font-medium text-primary hover:text-blue-500">
                    Войдите
                </a>
            </p>
        </div>
        
        <form class="mt-8 space-y-6" method="POST" action="{{ route('register') }}">
            @csrf
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Имя
                </label>
                <input id="name" name="name" type="text" required 
                    class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="Иван Иванов"
                    value="{{ old('name') }}">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Email адрес
                </label>
                <input id="email" name="email" type="email" autocomplete="email" required 
                    class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="name@example.com"
                    value="{{ old('email') }}">
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Пароль
                </label>
                <input id="password" name="password" type="password" autocomplete="new-password" required 
                    class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="••••••••">
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Подтверждение пароля
                </label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required 
                    class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                    placeholder="••••••••">
            </div>
            
            <div>
                <button type="submit" 
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Зарегистрироваться
                </button>
            </div>
            
            <p class="text-xs text-gray-500 text-center">
                Регистрируясь, вы соглашаетесь с 
                <a href="#" class="text-primary hover:underline">условиями использования</a> 
                и 
                <a href="#" class="text-primary hover:underline">политикой конфиденциальности</a>
            </p>
        </form>
    </div>
</div>
@endsection
