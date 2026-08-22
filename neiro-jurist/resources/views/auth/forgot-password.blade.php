@extends('layouts.app')

@section('title', 'Восстановление пароля — Нейро-юрист')

@section('content')
<div class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Восстановление пароля
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Введите ваш email и мы отправим инструкцию по сбросу пароля
            </p>
        </div>
        
        <form class="mt-8 space-y-6" method="POST" action="{{ route('password.request') }}">
            @csrf
            
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
                <button type="submit" 
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Отправить ссылку
                </button>
            </div>
            
            <div class="text-center">
                <a href="{{ route('login') }}" class="font-medium text-primary hover:text-blue-500">
                    ← Вернуться ко входу
                </a>
            </div>
        </form>
        
        @if(session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-center">
                {{ session('status') }}
            </div>
        @endif
    </div>
</div>
@endsection
