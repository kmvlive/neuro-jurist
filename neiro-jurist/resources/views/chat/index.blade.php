@extends('layouts.app')

@section('title', 'Чаты — Нейро-юрист')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Мои чаты</h1>
        <form method="POST" action="{{ route('chat.create') }}" class="inline">
            @csrf
            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700 flex items-center">
                <span class="mr-2">+</span> Новый чат
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-8 text-center text-gray-500">
            <div class="text-6xl mb-4">💬</div>
            <h3 class="text-xl font-semibold mb-2">У вас пока нет чатов</h3>
            <p class="mb-4">Создайте новый чат и получите юридическую консультацию от AI-ассистента</p>
            <form method="POST" action="{{ route('chat.create') }}" class="inline">
                @csrf
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Создать первый чат
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
