@extends('layouts.app')
@section('title', 'Оплата прошла')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-16 text-center">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
        <div class="text-6xl mb-4">✅</div>
        <h1 class="text-3xl font-bold mb-4 text-gray-900 dark:text-gray-100">Оплата прошла успешно!</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-8">Ваша подписка активирована. Можно возвращаться к работе.</p>
        <a href="{{ route('chat.show') }}" class="inline-block bg-primary text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-semibold">
            Перейти к чату →
        </a>
    </div>
</div>
@endsection
