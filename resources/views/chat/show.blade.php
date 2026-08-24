@extends('layouts.app')

@section('title', 'Чат #' . ($chatId ?? 'Новый') . ' — Нейро-юрист')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-[calc(100vh-200px)] flex flex-col">
    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('chat.show') }}" class="text-primary hover:underline">← Назад к чатам</a>
        @if($chatId)
        <form method="POST" action="{{ route('chat.destroy', $chatId) }}"
              onsubmit="return confirm('Вы уверены, что хотите удалить этот чат?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-800">Удалить чат</button>
        </form>
        @endif
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex-grow bg-white rounded-lg shadow overflow-hidden flex flex-col">
        <div class="flex-grow p-4 overflow-y-auto space-y-4" id="messages-container">
            @if($currentChat && $messages->count() > 0)
                @foreach($messages as $msg)
                    <div class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-lg px-4 py-2 {{ $msg->role === 'user' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-800' }}">
                            <p class="text-sm">{{ $msg->content }}</p>
                            <span class="text-xs opacity-75 mt-1 block">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="flex justify-start">
                    <div class="max-w-[80%] rounded-lg px-4 py-2 bg-gray-200 text-gray-800">
                        <p class="text-sm text-gray-900">Здравствуйте! Я ваш AI-юрист. Чем могу помочь?</p>
                        <span class="text-xs text-gray-500 mt-1 block">Только что</span>
                    </div>
                </div>
            @endif
        </div>

        <div class="border-t p-4 bg-gray-50">
            <form method="POST" action="{{ route('chat.send') }}" class="flex space-x-4">
                @csrf
                <input type="hidden" name="chat_id" value="{{ $chatId ?? '' }}">
                <textarea name="message" rows="2"
                    class="flex-grow px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary resize-none"
                    placeholder="Введите ваш вопрос..."
                    required></textarea>
                <button type="submit"
                    class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700 self-end">
                    Отправить
                </button>
            </form>
            <div class="text-xs text-gray-500 mt-2">
                Бесплатно: {{ $remainingMessages }} из {{ $freeLimit }} сообщений.
            </div>
        </div>
    </div>
</div>

@if($isGuest && !$canSend)
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md mx-4">
        <h3 class="text-xl font-bold mb-4">Бесплатный лимит исчерпан</h3>
        <p class="text-gray-600 mb-6">Зарегистрируйтесь, чтобы сохранить историю консультаций и продолжить общение.</p>
        <div class="flex space-x-4">
            <a href="{{ route('register') }}" class="flex-1 bg-primary text-white text-center py-2 rounded-lg hover:bg-blue-700">Зарегистрироваться</a>
            <a href="{{ route('login') }}" class="flex-1 border border-primary text-primary text-center py-2 rounded-lg hover:bg-blue-50">У меня уже есть аккаунт</a>
        </div>
    </div>
</div>
@endif

@if(!$isGuest && !$canSend)
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center">
        <h3 class="text-xl font-bold mb-4">Лимит бесплатных сообщений исчерпан</h3>
        <p class="text-gray-600 mb-6">Выберите тариф, чтобы продолжить пользоваться сервисом без ограничений.</p>
        <a href="{{ route('pricing') }}" class="inline-block bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700">Перейти к тарифам</a>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const container = document.getElementById("messages-container");
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
});
</script>
@endpush
